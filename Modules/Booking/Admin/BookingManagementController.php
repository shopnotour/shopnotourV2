<?php

namespace Modules\Booking\Admin;

use App\Http\Controllers\Controller;
use App\Service\SabreApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingPassenger;
//use App\Models\Transaction;
use Modules\Booking\Models\Bookingroute;
use Modules\Booking\Service\Sabre\SabreBookingResponseService;
use Modules\Booking\Service\Sabre\SabrePriceCheckPayloadBuilder;
use Modules\Booking\Service\TravelPortBookingXmlService;
use Modules\Booking\Service\TravelPortPNRResponseService;
use Modules\Booking\Service\TravelPortPNRRetrieveService;
use Modules\User\Models\Wallet\Transaction;
use mysql_xdevapi\Result;

class BookingManagementController extends Controller
{

    public function index(Request $request)
    {
        $query = Booking::with(['user', 'passengers', 'issuedBy'])
            ->orderBy('created_at', 'desc');

        // status explicitly request এ আছে কিনা check
        $statusRequested = $request->has('status');
        $selectedStatus  = $request->input('status', 'booked');

        if ($statusRequested && $request->filled('status')) {
            // specific status select করা হয়েছে
            $query->where('status', $selectedStatus);
        } elseif ($statusRequested && !$request->filled('status')) {
            // "All" select করা হয়েছে (status= খালি) — cancelled + pnr_pending বাদ
            $selectedStatus = '';
            $query->whereNotIn('status', ['cancelled', 'pnr_pending']);
        } else {
            // কোনো request নেই — default booked
             $query->whereIn('status', ['booked','issue_request']);
        }

        // Date range filter
        $dateColumn = in_array($request->date_column, [
            'booking_date', 'confirmed_at', 'ticket_issued_at', 'booking_cancel_at'
        ]) ? $request->date_column : 'booking_date';

        if ($request->filled('date_from')) {
            $query->whereDate($dateColumn, '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate($dateColumn, '<=', $request->date_to);
        }

        $rows          = $query->paginate(50)->withQueryString();
        $dateFrom      = $request->date_from;
        $dateTo        = $request->date_to;
        $dateColumnSel = $dateColumn;

        return view('Booking::admin.bookings.index', compact(
            'rows', 'selectedStatus', 'dateFrom', 'dateTo', 'dateColumnSel'
        ));
    }

// ─────────────────────────────────────────────────────────────────
// NEW: duplicate()
// Route: POST /bookings/{id}/duplicate
// ─────────────────────────────────────────────────────────────────

    public function duplicate(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $original = Booking::with(['passengers', 'routes'])->findOrFail($id);

            // ── 1. Booking copy ──
            $newBookingData = $original->toArray();

            // Remove fields যা নতুন booking এ থাকবে না
            unset(
                $newBookingData['id'],
                $newBookingData['created_at'],
                $newBookingData['updated_at'],
                $newBookingData['deleted_at'],
                $newBookingData['paid'],
                $newBookingData['pay_now'],
                $newBookingData['ticket_number'],
                $newBookingData['ticket_issued_at'],
                $newBookingData['ticket_details'],
                $newBookingData['pnr_id'],
                $newBookingData['pnr_raw_data'],
                $newBookingData['confirmed_at'],
                $newBookingData['issued_by'],
                $newBookingData['payment_id'],
                $newBookingData['wallet_transaction_id'],
                $newBookingData['wallet_credit_used'],
                $newBookingData['passengers'],  // relation array বাদ
                $newBookingData['routes'],
            );

            // নতুন code + status
            $newBookingData['code']       = 'BK' . strtoupper(substr(uniqid(), -8));
            $newBookingData['status']     = 'booked';
            $newBookingData['paid']       = 0;
            $newBookingData['pay_now']    = $original->total;
            $newBookingData['is_paid']    = 0;
            $newBookingData['create_user']= auth()->id();
            $newBookingData['created_at'] = now();
            $newBookingData['updated_at'] = now();

            $newBooking = Booking::create($newBookingData);

            // ── 2. Passengers copy ──
            foreach ($original->passengers as $pax) {
                $paxData = $pax->toArray();
                unset(
                    $paxData['id'],
                    $paxData['created_at'],
                    $paxData['updated_at'],
                    $paxData['deleted_at'],
                    $paxData['ticket_number'],
                    $paxData['ticket_issued_at'],
                    $paxData['status'],
                    $paxData['passport_media_id'],
                    $paxData['visa_media_id'],
                );
                $paxData['booking_id']   = $newBooking->id;
                $paxData['create_user']  = auth()->id();
                $paxData['created_at']   = now();
                $paxData['updated_at']   = now();

                Bookingroute::getModel(); // just to avoid unused import warning
                \Modules\Booking\Models\BookingPassenger::create($paxData);
            }

            // ── 3. Routes copy ──
            foreach ($original->routes as $route) {
                $routeData = $route->toArray();
                unset(
                    $routeData['id'],
                    $routeData['created_at'],
                    $routeData['updated_at'],
                    $routeData['deleted_at'],
                );
                $routeData['booking_id']   = $newBooking->id;
                $routeData['create_user']  = auth()->id();
                $routeData['created_at']   = now();
                $routeData['updated_at']   = now();

                Bookingroute::create($routeData);
            }

            DB::commit();

            Log::info('✅ Booking duplicated', [
                'original_id' => $original->id,
                'new_id'      => $newBooking->id,
                'new_code'    => $newBooking->code,
            ]);

            return response()->json([
                'success'  => true,
                'message'  => 'Booking duplicated successfully! New booking: ' . $newBooking->code,
                'new_id'   => $newBooking->id,
                'new_code' => $newBooking->code,
                'edit_url' => route('admin.bookings.edit', $newBooking->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Duplicate booking error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to duplicate: ' . $e->getMessage(),
            ], 500);
        }
    }

// ─────────────────────────────────────────────────────────────────
// NEW: edit()
// Route: GET /bookings/{id}/edit
// ─────────────────────────────────────────────────────────────────

    public function edit($id)
    {
        $booking = Booking::with([
            'user',
            'passengers',
            'routes',
            'issuedBy',
        ])->findOrFail($id);

        // Duplicate থেকে এলে flash message show করার জন্য
        $isDuplicate = session('is_duplicate', false);

        return view('Booking::admin.bookings.edit', compact('booking', 'isDuplicate'));
    }

// ─────────────────────────────────────────────────────────────────
// NEW: update()
// Route: PUT /bookings/{id}
// ─────────────────────────────────────────────────────────────────

    public function update(Request $request, $id)
    {
        $request->validate([
            'status'        => 'required|string',
            'source'        => 'required|string',
            'total'         => 'required|numeric|min:0',
            'paid'          => 'required|numeric|min:0',
            'routes'        => 'nullable|array',
            'passengers'    => 'nullable|array',
        ]);

        DB::beginTransaction();

        try {
            $booking = Booking::with(['user', 'passengers', 'routes'])->findOrFail($id);
            $oldTotal = $booking->total;

            // ── 1. Booking main fields update ──
            $booking->fill([
                'status'         => $request->status,
                'source'         => $request->source,
                'pnr_id'         => $request->pnr_id ? strtoupper(trim($request->pnr_id)) : $booking->pnr_id,
                'flight_from'    => $request->flight_from ? strtoupper($request->flight_from) : $booking->flight_from,
                'flight_to'      => $request->flight_to ? strtoupper($request->flight_to) : $booking->flight_to,
                'airline'        => $request->airline ?? $booking->airline,
                'flight_type'    => $request->flight_type ?? $booking->flight_type,
                'seat_class'     => $request->seat_class ?? $booking->seat_class,
                'total'          => $request->total,
                'paid'           => $request->paid,
                'pay_now'        => max(0, $request->total - $request->paid),
                'base_fee'       => $request->base_fee ?? $booking->base_fee,
                'total_fee'      => $request->total_fee ?? $booking->total_fee,
                'supplier_fee'   => $request->supplier_fee ?? $booking->supplier_fee,
                'ticketing_fee'  => $request->ticketing_fee ?? $booking->ticketing_fee,
                'coupon_amount'  => $request->coupon_amount ?? $booking->coupon_amount,
                'currency'       => $request->currency ? strtoupper($request->currency) : $booking->currency,
                'email'          => $request->email ?? $booking->email,
                'first_name'     => $request->first_name ?? $booking->first_name,
                'last_name'      => $request->last_name ?? $booking->last_name,
                'phone'          => $request->phone ?? $booking->phone,
                'customer_notes' => $request->customer_notes ?? $booking->customer_notes,
                'booking_date'   => $request->booking_date ?? $booking->booking_date,
                'start_date'     => $request->start_date ?? $booking->start_date,
                'end_date'       => $request->end_date ?? $booking->end_date,
                'update_user'    => auth()->id(),
            ]);

            // confirmed_at set করো যদি status booked/issued/ticketed হয়
            if (in_array($request->status, ['booked','issued','ticketed','completed']) && !$booking->confirmed_at) {
                $booking->confirmed_at = $request->confirmed_at ?? now();
            } elseif ($request->filled('confirmed_at')) {
                $booking->confirmed_at = $request->confirmed_at;
            }

            $booking->save();

            // ── 2. Passengers update ──
            if ($request->has('passengers')) {
                foreach ($request->passengers as $paxData) {
                    if (empty($paxData['id'])) continue;

                    $pax = \Modules\Booking\Models\BookingPassenger::where('id', $paxData['id'])
                        ->where('booking_id', $booking->id)
                        ->first();

                    if (!$pax) continue;

                    $pax->fill([
                        'title'                => $paxData['title']                ?? $pax->title,
                        'first_name'           => $paxData['first_name']           ?? $pax->first_name,
                        'last_name'            => $paxData['last_name']            ?? $pax->last_name,
                        'email'                => $paxData['email']                ?? $pax->email,
                        'phone'                => $paxData['phone']                ?? $pax->phone,
                        'gender'               => $paxData['gender']               ?? $pax->gender,
                        'dob'                  => $paxData['dob']                  ?? $pax->dob,
                        'country'              => $paxData['country']              ? strtoupper($paxData['country']) : $pax->country,
                        'passport_number'      => $paxData['passport_number']      ?? $pax->passport_number,
                        'passport_expiry_date' => $paxData['passport_expiry_date'] ?? $pax->passport_expiry_date,
                        'passenger_type_code'  => $paxData['passenger_type_code']  ?? $pax->passenger_type_code,
                        'ticket_number'        => $paxData['ticket_number']        ?? $pax->ticket_number,
                        'base'                 => $paxData['base']                 ?? $pax->base,
                        'tax'                  => $paxData['tax']                  ?? $pax->tax,
                        'total'                => $paxData['total']                ?? $pax->total,
                        'cabin'                => $paxData['cabin']                ?? $pax->cabin,
                        'update_user'          => auth()->id(),
                    ]);
                    $pax->save();
                }
            }

            // ── 3. Routes update ──
            if ($request->has('routes')) {
                $submittedIds = [];

                foreach ($request->routes as $routeData) {
                    if (!empty($routeData['id'])) {
                        // existing route update
                        $route = Bookingroute::where('id', $routeData['id'])
                            ->where('booking_id', $booking->id)
                            ->first();

                        if ($route) {
                            $route->fill([
                                'departure_iata_code' => $routeData['departure_iata_code'] ? strtoupper($routeData['departure_iata_code']) : $route->departure_iata_code,
                                'arrival_iata_code'   => $routeData['arrival_iata_code']   ? strtoupper($routeData['arrival_iata_code'])   : $route->arrival_iata_code,
                                'flight_number'       => $routeData['flight_number']       ?? $route->flight_number,
                                'departure_at'        => $routeData['departure_at']        ?? $route->departure_at,
                                'arrival_at'          => $routeData['arrival_at']          ?? $route->arrival_at,
                                'cabin'               => $routeData['cabin']               ?? $route->cabin,
                                'update_user'         => auth()->id(),
                            ]);
                            $route->save();
                            $submittedIds[] = $route->id;
                        }
                    } else {
                        // নতুন route
                        if (empty($routeData['departure_iata_code']) && empty($routeData['arrival_iata_code'])) continue;

                        $newRoute = Bookingroute::create([
                            'booking_id'          => $booking->id,
                            'departure_iata_code' => $routeData['departure_iata_code'] ? strtoupper($routeData['departure_iata_code']) : null,
                            'arrival_iata_code'   => $routeData['arrival_iata_code']   ? strtoupper($routeData['arrival_iata_code'])   : null,
                            'flight_number'       => $routeData['flight_number']       ?? null,
                            'departure_at'        => $routeData['departure_at']        ?? null,
                            'arrival_at'          => $routeData['arrival_at']          ?? null,
                            'cabin'               => $routeData['cabin']               ?? 'Economy',
                            'create_user'         => auth()->id(),
                        ]);
                        $submittedIds[] = $newRoute->id;
                    }
                }

                // যে routes গুলো form এ ছিল না সেগুলো delete করো
                if (!empty($submittedIds)) {
                    Bookingroute::where('booking_id', $booking->id)
                        ->whereNotIn('id', $submittedIds)
                        ->delete();
                }
            }

            // ── 4. Wallet deduction (optional) ──
            if ($request->boolean('deduct_wallet') && $booking->user) {
                $deductAmount  = (float) ($request->wallet_deduct_amount ?? 0);
                $deductRemarks = $request->wallet_deduct_remarks ?? 'Payment for booking ' . $booking->code;

                if ($deductAmount > 0) {
                    if ($booking->user->credit_balance < $deductAmount) {
                        DB::rollBack();
                        return back()->with('error', 'Insufficient credit balance! Available: ' . format_money_main($booking->user->credit_balance));
                    }

                    // Credit balance কাটো
                    $booking->user->decrement('credit_balance', $deductAmount);

                    // Transaction create
                    \Modules\User\Models\Wallet\Transaction::create([
                        'user_id'          => $booking->user->id,
                        'booking_id'       => $booking->id,
                        'type'             => 'debit',
                        'transaction_type' => 'credit_balance_payment',
                        'amount'           => $deductAmount,
                        'status'           => 'completed',
                        'remarks'          => $deductRemarks,
                        'reference'        => 'Booking edit payment — ' . $booking->code,
                        'ref_id'           => $booking->code ?? $booking->id,
                        'create_user'      => auth()->id(),
                        'deposit_date'     => now(),
                    ]);

                    // Booking paid amount update
                    $newPaid = $booking->paid + $deductAmount;
                    $booking->paid   = $newPaid;
                    $booking->pay_now = max(0, $booking->total - $newPaid);
                    $booking->save();

                    Log::info('💰 Wallet deducted on booking edit', [
                        'booking_id' => $booking->id,
                        'amount'     => $deductAmount,
                        'user_id'    => $booking->user->id,
                    ]);
                }
            }

            DB::commit();

            Log::info('✅ Booking updated', ['booking_id' => $booking->id, 'admin_id' => auth()->id()]);

            return redirect()
                ->route('admin.bookings.edit', $booking->id)
                ->with('success', 'Booking updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking update error: ' . $e->getMessage());
            return back()->with('error', 'Failed to update: ' . $e->getMessage());
        }
    }



    /**
     * ✅ ticketCancel() — Ticket cancel with optional reason
     * Route: POST /bookings/{id}/ticket-cancel
     */
    public function ticketCancel(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $booking = Booking::with('user')->findOrFail($id);
        $reason  = $request->reason ?? '';

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            // Booking status cancelled
            $booking->status = 'cancelled';
            $booking->save();

            // শুধুমাত্র paid > 0 এবং user থাকলে transaction + credit balance
            if ($booking->paid > 0 && $booking->user) {

                \Modules\User\Models\Wallet\Transaction::create([
                    'booking_id'       => $booking->id,
                    'user_id'          => $booking->user->id,
                    'type'             => 'credit',
                    'transaction_type' => 'refund',
                    'amount'           => $booking->paid,
                    'status'           => 'confirmed',
                    'remarks'          => $reason ?: 'Ticket cancelled by admin. Booking: ' . ($booking->code ?? '#' . $booking->id),
                    'reference'        => $reason,
                    'ref_id'           => $booking->code ?? $booking->id,
                    'create_user'      => auth()->id(),
                    'deposit_date'     => now(),
                ]);

                // User credit_balance এ refund যোগ
                $booking->user->credit_balance += $booking->paid;
                $booking->user->save();
            }

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'success' => true,
                'message' => $booking->paid > 0
                    ? __('Ticket cancelled. ৳:amount refunded to credit balance.', ['amount' => number_format($booking->paid, 2)])
                    : __('Ticket cancelled successfully'),
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Ticket cancel error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => __('Failed to cancel ticket. Please try again.'),
            ], 500);
        }
    }



    /**
     * ✅ updatePnr() — PNR, Source, Status update
     * Route: POST /bookings/{id}/update-pnr
     */
    public function updatePnr(Request $request, $id)
    {
        $request->validate([
            'pnr_id' => 'required|string|max:20',
            'source' => 'required|in:sabre,travelport,galileo,amadeus,manual',
            'status' => 'required|in:issue_request,pending,paid,booked,issued,ticketed,completed,cancelled,failed,refunded',
        ]);

        $booking = Booking::findOrFail($id);

        $booking->pnr_id = strtoupper(trim($request->pnr_id));
        $booking->source = $request->source;
        $booking->status = $request->status;

        // Status booked/confirmed হলে confirmed_at set করো
        if (in_array($request->status, ['booked', 'issued', 'ticketed', 'completed']) && !$booking->confirmed_at) {
            $booking->confirmed_at = now();
        }

        $booking->save();

        return response()->json([
            'success' => true,
            'message' => __('PNR, Source and Status updated successfully'),
            'data'    => [
                'pnr_id' => $booking->pnr_id,
                'source' => $booking->source,
                'status' => $booking->status,
            ]
        ]);
    }


    /**
     * ✅ updatePassengerPassport() — Passport number, expiry, passport_media_id, visa_media_id update
     * MediaFile model ব্যবহার করে image store হবে
     * Route: POST /bookings/passenger/{id}/update-passport
     */
    public function updatePassengerPassport(Request $request, $id)
    {
        $request->validate([
            'passport_number'      => 'nullable|string|max:20',
            'passport_expiry_date' => 'nullable|date',
            'dob'                  => 'nullable|date',
            'passport_image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'visa_image'           => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $passenger = \Modules\Booking\Models\BookingPassenger::findOrFail($id);

        if ($request->filled('passport_number')) {
            $passenger->passport_number = $request->passport_number;
        }

        if ($request->filled('passport_expiry_date')) {
            $passenger->passport_expiry_date = $request->passport_expiry_date;
        }

        if ($request->filled('dob')) {
            $passenger->dob = $request->dob;
        }

        // ✅ Passport Image — MediaFile এ store করো
        if ($request->hasFile('passport_image')) {
            $mediaFile = $this->storePassengerMediaFile(
                $request->file('passport_image'),
                'passengers/passport'
            );
            if ($mediaFile) {
                $passenger->passport_media_id = $mediaFile->id;
            }
        }

        // ✅ Visa Image — MediaFile এ store করো
        if ($request->hasFile('visa_image')) {
            $mediaFile = $this->storePassengerMediaFile(
                $request->file('visa_image'),
                'passengers/visa'
            );
            if ($mediaFile) {
                $passenger->visa_media_id = $mediaFile->id;
            }
        }

        $passenger->save();

        // Relation fresh load
        $passenger->load(['passportMedia', 'visaMedia']);

        return response()->json([
            'success' => true,
            'message' => __('Passenger updated successfully'),
            'data'    => [
                'passport_number'    => $passenger->passport_number,
                'passport_image_url' => $passenger->passportMedia
                    ? get_file_url($passenger->passportMedia->id)
                    : null,
                'visa_image_url'     => $passenger->visaMedia
                    ? get_file_url($passenger->visaMedia->id)
                    : null,
            ]
        ]);
    }

    /**
     * ✅ Private helper — file upload করে MediaFile record create করে
     */
    private function storePassengerMediaFile($file, $folder = 'passengers')
    {
        try {
            $driver    = config('filesystems.default', 'uploads');
            $extension = $file->getClientOriginalExtension();
            $fileName  = \Illuminate\Support\Str::slug(
                    pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)
                ) . '_' . time() . '.' . $extension;
            $filePath  = $folder . '/' . $fileName;

            \Illuminate\Support\Facades\Storage::disk($driver)->put(
                $filePath,
                file_get_contents($file->getRealPath()),
                'public'
            );

            return \Modules\Media\Models\MediaFile::create([
                'file_name'      => $fileName,
                'file_path'      => $filePath,
                'file_size'      => $file->getSize(),
                'file_type'      => $file->getMimeType(),
                'file_extension' => $extension,
                'driver'         => $driver,
                'is_private'     => 0,
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Passenger MediaFile store error: ' . $e->getMessage());
            return null;
        }
    }
    public function modal(Request $request, $id)
    {
        try {
            $booking = Booking::with([
                'passengers',
                'passengers.passportMedia',  // ✅ passport image
                'passengers.visaMedia',       // ✅ visa image
                'routes',
            ])->findOrFail($id);

            return view('Booking::admin.bookings.modal', compact('booking'));

        } catch (\Exception $e) {
            return response()->json([
                'error'   => 'Booking not found',
                'message' => $e->getMessage()
            ], 404);
        }
    }
    public function show($id)
    {
        $booking = Booking::with(['passengers', 'user'])->findOrFail($id);
        return view('Booking::admin.bookings.show', compact('booking'));
    }

    public function setPaid(Request $request, $id)
    {
        Log::info('💰 Set Paid Request', [
            'booking_id' => $id,
            'amount'     => $request->remain,
            'admin_id'   => auth()->id()
        ]);

        try {
            $request->validate([
                'remain' => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();

            $booking    = Booking::findOrFail($id);
            $user       = $booking->user;
            $paidAmount = (float) $request->remain;

            // ✅ Credit balance check
            if (!$user || $user->credit_balance < $paidAmount) {
                return response()->json([
                    'status'  => false,
                    'success' => false,
                    'message' => 'Insufficient credit balance!' .
                        ($user ? ' Available: ৳' . number_format($user->credit_balance, 2) : ''),
                ], 422);
            }

            $oldPaid = (float) $booking->paid;
            $newPaid = $oldPaid + $paidAmount;

            // ✅ Credit balance কাটো
            $user->decrement('credit_balance', $paidAmount);

            // ✅ Booking update
            $booking->update([
                'paid'    => $newPaid,
                'pay_now' => max(0, $booking->total - $newPaid),
            ]);

            // ✅ Transaction record
            Transaction::create([
                'user_id'          => $user->id,
                'booking_id'       => $booking->id,
                'ref_id'           => auth()->id(),
                'type'             => 'debit',
                'transaction_type' => 'credit_balance_payment',
                'amount'           => $paidAmount,
                'status'           => 'completed',
                'reference'        => 'Credit balance payment - Booking #' . $booking->code,
                'remarks'          => 'Credit balance deducted for booking #' . $booking->code,
                'meta'             => json_encode([
                    'old_credit_balance' => $user->credit_balance + $paidAmount,
                    'new_credit_balance' => $user->credit_balance,
                    'old_paid'           => $oldPaid,
                    'new_paid'           => $newPaid,
                    'booking_code'       => $booking->code,
                    'payment_method'     => 'credit_balance',
                    'admin_id'           => auth()->id(),
                ]),
                'create_user' => auth()->id(),
                'created_at'  => now(),
                'deposit_date'     => now(),
            ]);

            // ✅ Fully paid হলে status update
            if ($newPaid >= $booking->total && $booking->status == 'pending') {
                $booking->update(['status' => 'paid']);
            }

            DB::commit();

            Log::info('✅ Payment Added Successfully', [
                'booking_id'   => $booking->id,
                'old_paid'     => $oldPaid,
                'new_paid'     => $newPaid,
                'amount_added' => $paidAmount,
            ]);

            return response()->json([
                'status'  => true,
                'success' => true,
                'message' => "Payment added successfully!\n\nCredit Used: ৳" . number_format($paidAmount, 2) .
                    "\nTotal Paid: ৳" . number_format($newPaid, 2) .
                    "\nRemaining Credit: ৳" . number_format($user->credit_balance, 2),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Set Paid Error', ['error' => $e->getMessage()]);

            return response()->json([
                'status'  => false,
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->ids;
        $action = $request->action;

        Log::info('📋 Booking Bulk Action', [
            'action' => $action,
            'ids' => $ids,
            'admin_id' => auth()->id()
        ]);

        if (empty($ids) || empty($action)) {
            return back()->with('error', 'Please select items and action');
        }

        try {
            switch ($action) {
                case 'delete':
                    $deleted = Booking::whereIn('id', $ids)->delete();
                    Log::info('✅ Bulk Deleted', ['count' => $deleted]);
                    return back()->with('success', "Deleted {$deleted} booking(s)");

                default:
                    // Update status
                    if (in_array($action, ['pending', 'confirmed', 'paid', 'booked', 'ticketed', 'cancelled', 'failed'])) {
                        $updated = Booking::whereIn('id', $ids)->update(['status' => $action]);
                        Log::info('✅ Bulk Status Updated', ['status' => $action, 'count' => $updated]);
                        return back()->with('success', "Updated {$updated} booking(s) to {$action}");
                    }

                    return back()->with('error', 'Invalid action');
            }
        } catch (\Exception $e) {
            Log::error('❌ Bulk Action Error', [
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function priceCheck(string $id)
    {

        try {
            $booking = Booking::find($id);

            if (!$booking) {
                return view('Booking::admin.page.price-check', [
                    'priceResponse' => [
                        'success' => false,
                        'error' => 'Booking not found'
                    ],
                    'bookingId' => $id
                ]);
            }
            if ($booking->source == 'travelport') {
                $travelportbookingxml= new TravelPortBookingXmlService();
                $priceResponse=$travelportbookingxml->buildPriceRequestXml($id);
//return $priceResponse;
            } elseif ($booking->source == 'sabre') {
 // Get stored flight data
                $pricePayloadBuilder = new SabrePriceCheckPayloadBuilder();
                $priceResponse =$pricePayloadBuilder->getPriceForBooking($id);

//                return $priceResponse;
            } else {
                return view('Booking::admin.page.pnr_details_new', [
                    'bookingData' => [
                        'success' => false,
                        'error' => 'Unsupported booking source: ' . $booking->source
                    ]
                ]);
            }

            // Add success flag if not present
            if (!isset($priceResponse['success'])) {
                $priceResponse['success'] = true;
            }

            return view('Booking::admin.page.price-check', [
                'priceResponse' => $priceResponse,
                'bookingId' => $id,
                'booking' => $booking
            ]);

        } catch (\Exception $e) {
            \Log::error('Price Check Error', [
                'booking_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('Booking::admin.page.price-check', [
                'priceResponse' => [
                    'success' => false,
                    'error' => 'Failed to check price: ' . $e->getMessage()
                ],
                'bookingId' => $id
            ]);
        }
    }

    public function updatePrice(Request $request, $bookingId)
    {
//        return $request;
        try {
            DB::beginTransaction();

            $booking = Booking::findOrFail($bookingId);

            $totalcal = $request->total_price + ($booking->ticketing_fee + $booking->supplier_fee) - $booking->coupon_amount;

            $booking->update([
                'total' => $totalcal,
                'base_fee' => $request->base_price ?? 0,
                'total_fee' => $request->total_tax ?? 0,
                'currency' => $request->currency ?? 'BDT',
                'price_raw_data' => json_encode($request),
            ]);

            // Update Booking Table
//            $booking->update([
//                'total' => $request->total_price,
//                'currency' => $request->currency,
//                'base_fee' => $request->base_price,
//                'tax_amount' => $request->total_tax,
//                'solution_key' => $request->solution_key,
//                'fare_quote_date' => $request->quote_date,
//                'price_updated_at' => now(),
//            ]);

            // Update Passengers with their specific prices
            $passengerPrices = $request->passenger_prices;

            foreach ($booking->passengers as $passenger) {
                $paxType = $passenger->passenger_type_code; // ADT, CNN, INF

                if (isset($passengerPrices[$paxType])) {
                    $priceData = $passengerPrices[$paxType];

                    $passenger->update([
                        'total' => $priceData['total'],
                        'base' => $priceData['base_fare'],
                        'tax' => $priceData['tax'],
//                        'baggage_allowance' => $priceData['baggage'],
//                        'cabin_baggage' => $priceData['cabin_bag'],
//                        'is_refundable' => $priceData['refundable'],
//                        'change_penalty' => $priceData['change_penalty'],
//                        'cancel_penalty' => $priceData['cancel_penalty'],
//                        'fare_basis' => $priceData['fare_basis'],
                    ]);
                }
            }

            // Optionally store segment info in a meta table or JSON field
//            if ($request->has('segments')) {
//                $booking->update([
//                    'flight_segments' => json_encode($request->segments)
//                ]);
//            }

            DB::commit();

            return redirect()
                ->route('bookings.index')
                ->with('success', 'Price updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Price Update Error', [
                'booking_id' => $bookingId,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Failed to update price: ' . $e->getMessage());
        }
    }


    public function retrievePNR(Request $request, string $id = null)
    {

        try {
            $users   = null;
            $booking = null;

            if ($id == null) {
                $source  = $request->input('source');
                $pnrCode = $request->input('pnr');
            } else {
                $booking = Booking::where('id', $id)->first();

                if ($booking) {
                    $source  = $booking->source;
                    $pnrCode = $booking->pnr_id;
                }
            }

            if ($pnrCode && $source) {

                $booking = $booking ?? Booking::where('pnr_id', $pnrCode)
                    ->where('source', $source)
                    ->first();

                // ── User logic ──────────────────────────────────────────────
                if ($booking && $booking->customer_id) {

                    $users = \App\Models\User::select('id', 'name')
                        ->where('id', $booking->customer_id)
                        ->first();
                } else {

                    $users = \DB::table('users')
                        ->select('id', 'name')
                        ->orderBy('name')
                        ->get(); // Collection
                }

                if ($source == 'travelport') {
//                    return $pnrCode;
                    $pnrService = new TravelPortPNRRetrieveService();
                    $result = $pnrService->retrievePNR($pnrCode);

                    if ($booking) {
                        $ticketDate = null;

                        if (!empty($result['action_status']['ticket_date'])) {
                            $ticketDate = \Carbon\Carbon::parse($result['action_status']['ticket_date'])
//                                ->setTimezone('Asia/Dhaka')
                                ->format('Y-m-d H:i:s');
                        }

                        $booking->update([
                            'pnr_raw_data'   => json_encode($result),
                            'booking_date'   => $ticketDate,
                        ]);
                    }
//                    return $result;
                } elseif ($source == 'sabre') {
//                    return 'sarowar';
                    $sabreBookingService = new SabreApiService();
                    $sabreData = $sabreBookingService->getPnr($pnrCode);
//                    dd($sabreData);
//                    return $sabreData;
                    $result = (new SabreBookingResponseService())->parseGetReservationResponse($sabreData);
//return $result;
                    if ($booking) {
                        $ticketDate = null;

                        if (!empty($result['action_status']['ticket_date'])) {
                            $ticketDate = \Carbon\Carbon::parse($result['action_status']['ticket_date'])
//                                ->setTimezone('Asia/Dhaka')
                                ->format('Y-m-d H:i:s');
                        }

                        $booking->update([
                            'pnr_raw_data'   => json_encode($result),
                            'booking_date'   => $ticketDate,
                        ]);
                    }

                } else {
                    return view('Booking::admin.page.pnr_details_new', [
                        'bookingData'     => ['success' => false, 'error' => 'Unsupported source: ' . $source],
                        'existingBooking' => null,
                        'users'           => $users,
                    ]);
                }

                return view('Booking::admin.page.pnr_details_new', [
                    'bookingData'     => $result,
                    'searchPnr'       => $pnrCode,
                    'searchSource'    => $source,
                    'existingBooking' => $booking ?? null,
                    'users'           => $users,
                ]);

            } else {
                return redirect()->back()->with('error', 'PNR Not Found');
            }

        } catch (\Exception $e) {
            \Log::error('PNR Retrieve Error', [
                'booking_id' => $id,
                'pnr'        => $request->input('pnr'),
                'source'     => $request->input('source'),
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString()
            ]);

            return view('Booking::admin.page.pnr_details_new', [
                'bookingData'     => ['success' => false, 'error' => 'Failed to retrieve PNR: ' . $e->getMessage()],
                'existingBooking' => null,
                'users'           => \App\Models\User::select('id', 'name')->get(),
            ]);
        }
    }

//    public function retrievePNR(Request $request, string $id = null)
//    {
//
//        try {
//
//            if ($id == null){
//                $source = $request->input('source');
//                $pnrCode = $request->input('pnr');
//            }else{
//
//                $booking = Booking::where('id', $id)->first();
//
//                if ($booking) {
//                    $source  = $booking->source;
//                    $pnrCode = $booking->pnr_id;
//
//                    // ✅ booking এর customer টা নাও
//                    $users = \App\Models\User::select('id', 'name')
//                        ->where('id', $booking->customer_id)
//                        ->first();
//                } else {
//                    // ✅ booking নেই — সব user দেখাও
//                    $users = \App\Models\User::select('id', 'name')->get();
//                }
//
//            }
////            return $pnrCode;
////            $source = $request->input('source');
////            $pnrCode = $request->input('pnr');
//
//            // Case 1: Search by PNR code directly (from search form)
//            if ($pnrCode && $source) {
//                \Log::info('🔍 Searching PNR directly', [
//                    'pnr' => $pnrCode,
//                    'source' => $source
//                ]);
//
//                // First check if booking exists with this PNR
//                $booking = Booking::where('pnr_id', $pnrCode)
//                    ->where('source', $source)
//                    ->first();
//
//                // Retrieve PNR from API
//                if ($source == 'travelport') {
//                    $pnrService = new TravelPortPNRRetrieveService();
//                    $result = $pnrService->retrievePNR($pnrCode);
//                    dd($result);
//                } elseif ($source == 'sabre') {
//                    $sabreBookingService = new SabreApiService();
//                    $sabreData = $sabreBookingService->getPnr($pnrCode);
//
//                    $result = (new SabreBookingResponseService())->parseGetReservationResponse($sabreData);
//
//                    if ($booking) {
//                        $booking->update([
//                            'pnr_raw_data' => json_encode($result),
//                        ]);
//                    }
////                    return $result;
////                    $pnrResponse = [
////                        'success' => true,
////                        ...$sabreData
////                    ];
//                } else {
//                    return view('Booking::admin.page.pnr_details_new', [
//                        'bookingData' => [
//                            'success' => false,
//                            'error' => 'Unsupported source: ' . $source
//                        ],
//                        'existingBooking' => null
//                    ]);
//                }
////                return $result;
//                return view('Booking::admin.page.pnr_details_new', [
//                    'bookingData'     => $result,      // parsed array (success, booking_id, passengers, segments, pricing, fare_rules ইত্যাদি)
//                    'searchPnr'       => $pnrCode,
//                    'users'           => $users,
//                    'searchSource'    => $source,          // 'sabre' or 'travelport'
//                    'existingBooking' => '', // Booking model অথবা null
//                ]);
//
//                // Add existing booking info if found
////                if ($booking) {
////                    $pnrResponse['existingBooking'] = [
////                        'id' => $booking->id,
////                        'code' => $booking->code,
////                        'status' => $booking->status,
////                        'created_at' => $booking->created_at->format('d M Y'),
////                    ];
////                }
////
////                return view('Booking::admin.page.pnr_details_new', [
////                    'bookingData' => $pnrResponse,
////                    'existingBooking' => $booking,
////                    'searchSource' => $source,
////                    'searchPnr' => $pnrCode
////                ]);
//            }else{
//                return redirect()->back()->with('error', 'PNR Not Found');
//            }
//
//        } catch (\Exception $e) {
//            \Log::error('PNR Retrieve Error', [
//                'booking_id' => $id,
//                'pnr' => $request->input('pnr'),
//                'source' => $request->input('source'),
//                'error' => $e->getMessage(),
//                'trace' => $e->getTraceAsString()
//            ]);
//
//            return view('Booking::admin.page.pnr_details_new', [
//                'bookingData' => [
//                    'success' => false,
//                    'error' => 'Failed to retrieve PNR: ' . $e->getMessage()
//                ],
//                'existingBooking' => null
//            ]);
//        }
//    }

//    public function assignTickets(Request $request)
//    {
////        return $request;
//        try {
//            // Validate
//            $request->validate([
//                'booking_id' => 'required|integer|exists:bravo_bookings,id',
//                'passengers' => 'required|array|min:1',
//                'passengers.*.id' => 'required|integer',
////                'passengers.*.ticket_number' => 'required|string|size:13',
//            ]);
//
//            $bookingId = $request->booking_id;
//            $passengers = $request->passengers;
//
//            \Log::info('📥 Assign Tickets Request', [
//                'booking_id' => $bookingId,
//                'passengers' => $passengers
//            ]);
//
//            // Update passengers
//            $updated = 0;
//            foreach ($passengers as $passengerData) {
//                $passenger = \Modules\Booking\Models\BookingPassenger::find($passengerData['id']);
//
//                if ($passenger && $passenger->booking_id == $bookingId) {
//                    $passenger->ticket_number = $passengerData['ticket_number'];
//                    $passenger->save();
//                    $updated++;
//                }
//            }
//
//            // Update booking status
//            $booking = \Modules\Booking\Models\Booking::find($bookingId);
//            if ($booking) {
//                $totalPassengers = $booking->passengers()->count();
//                $ticketedPassengers = $booking->passengers()->whereNotNull('ticket_number')->count();
//
//                if ($totalPassengers == $ticketedPassengers && $ticketedPassengers > 0) {
//                    $booking->status = 'ticketed';
//                    $booking->save();
//                }
//            }
//
//            return response()->json([
//                'success' => true,
//                'message' => "Successfully assigned {$updated} ticket(s)!"
//            ]);
//
//        } catch (\Illuminate\Validation\ValidationException $e) {
//            return response()->json([
//                'success' => false,
//                'message' => 'Validation failed',
//                'errors' => $e->errors()
//            ], 422);
//
//        } catch (\Exception $e) {
//            \Log::error('❌ Assign Tickets Error: ' . $e->getMessage());
//
//            return response()->json([
//                'success' => false,
//                'message' => 'Failed to assign tickets: ' . $e->getMessage()
//            ], 500);
//        }
//    }

    /**
     * ✅ Assign Tickets to Passengers
     */
    public function assignTickets(Request $request)
    {
        try {
            // Validate
            $request->validate([
                'booking_id' => 'required|integer|exists:bravo_bookings,id',
                'passengers' => 'required|array|min:1',
                'passengers.*.id' => 'required|integer',
//                'passengers.*.ticket_number' => 'required|string|size:13',
            ]);

            $bookingId = $request->booking_id;
            $passengers = $request->passengers;

            \Log::info('📥 Assign Tickets Request', [
                'booking_id' => $bookingId,
                'passengers' => $passengers
            ]);

            // ✅ Collect all ticket numbers for booking table
            $allTicketNumbers = [];

            // Update passengers
            $updated = 0;
            foreach ($passengers as $passengerData) {
                $passenger = \Modules\Booking\Models\BookingPassenger::find($passengerData['id']);

                if ($passenger && $passenger->booking_id == $bookingId) {
                    $passenger->ticket_number = $passengerData['ticket_number'];
                    $passenger->ticket_issued_at = now();
                    $passenger->save();

                    // ✅ Collect ticket number
                    $allTicketNumbers[] = $passengerData['ticket_number'];

                    $updated++;

                    \Log::info('✅ Ticket assigned to passenger', [
                        'passenger_id' => $passenger->id,
                        'ticket_number' => $passengerData['ticket_number']
                    ]);
                }
            }

            // ✅ Update booking table
            $booking = \Modules\Booking\Models\Booking::find($bookingId);
            if ($booking) {
                $totalPassengers = $booking->passengers()->count();
                $ticketedPassengers = $booking->passengers()->whereNotNull('ticket_number')->count();

                // ✅ Get all ticket numbers from all passengers
                $allBookingTickets = $booking->passengers()
                    ->whereNotNull('ticket_number')
                    ->pluck('ticket_number')
                    ->toArray();

                // ✅ Update booking fields
                $booking->ticket_number = json_encode($allBookingTickets); // JSON array
                $booking->ticket_issued_at = now(); // Current timestamp

                // ✅ Prepare ticket_details (JSON array of all tickets)
                $ticketDetails = [];
                foreach ($booking->passengers as $passenger) {
                    if ($passenger->ticket_number) {
                        $ticketDetails[] = [
                            'passenger_id' => $passenger->id,
                            'passenger_name' => $passenger->first_name . ' ' . $passenger->last_name,
                            'ticket_number' => $passenger->ticket_number,
                            'traveler_type' => $passenger->traveler_type ?? 'ADULT',
                            'issued_at' => now()->toDateTimeString()
                        ];
                    }
                }
                $booking->ticket_details = json_encode($ticketDetails);

                // ✅ Update status to 'ticketed' if all passengers have tickets
                if ($totalPassengers == $ticketedPassengers && $ticketedPassengers > 0) {
                    $booking->status = 'ticketed';
                }

                $booking->save();

                \Log::info('✅ Booking updated', [
                    'booking_id' => $bookingId,
                    'ticket_number' => $booking->ticket_number,
                    'ticket_issued_at' => $booking->ticket_issued_at,
                    'status' => $booking->status
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully assigned {$updated} ticket(s)!",
                'data' => [
                    'updated_count' => $updated,
                    'booking_id' => $bookingId,
                    'ticket_numbers' => $allTicketNumbers,
                    'booking_status' => $booking->status ?? null
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('❌ Validation Error', ['errors' => $e->errors()]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', array_map(function($errors) {
                        return implode(', ', $errors);
                    }, $e->errors())),
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            \Log::error('❌ Assign Tickets Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign tickets: ' . $e->getMessage()
            ], 500);
        }

    }

    public function assignTicketsFromPNR(Request $request)
    {
//        return $request;
        try {
            $validated = $request->validate([
                'pnr' => 'required|string',
                'source' => 'required|in:sabre,travelport',
                'booking_data' => 'required|array',
            ]);
//return $validated;
            $pnr = $validated['pnr'];
            $source = $validated['source'];
            $bookingData = $validated['booking_data'];

            \Log::info('📥 Auto Assign Tickets from PNR', [
                'pnr' => $pnr,
                'source' => $source
            ]);

            // Check if booking exists with this PNR
            $booking = \Modules\Booking\Models\Booking::where('pnr_id', $pnr)
                ->where('source', $source)
                ->first();

            if ($booking) {
                // UPDATE EXISTING BOOKING
                return $this->updateExistingBooking($booking, $bookingData);
            } else {
                // CREATE NEW BOOKING
                return $this->createNewBookingFromPNR($pnr, $source, $bookingData);
            }

        } catch (\Exception $e) {
            \Log::error('❌ Auto Assign from PNR Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // app/Http/Controllers/Admin/BookingController.php

//    public function assignTickets(Request $request)
//    {
//        try {
//            $validated = $request->validate([
//                'pnr' => 'required|string',
//                'source' => 'required|in:sabre,travelport',
//                'booking_data' => 'required|array',
//            ]);
//
//            $pnr = $validated['pnr'];
//            $source = $validated['source'];
//            $bookingData = $validated['booking_data'];
//
//            // Check if booking exists with this PNR
//            $booking = Booking::where('pnr_code', $pnr)
//                ->where('pnr_source', $source)
//                ->first();
//
//            if ($booking) {
//                // UPDATE EXISTING BOOKING
//                return $this->updateExistingBooking($booking, $bookingData);
//            } else {
//                // CREATE NEW BOOKING
//                return $this->createNewBookingFromPNR($pnr, $source, $bookingData);
//            }
//
//        } catch (\Exception $e) {
//            \Log::error('Assign Tickets Error: ' . $e->getMessage());
//            return response()->json([
//                'success' => false,
//                'message' => 'Error: ' . $e->getMessage()
//            ], 500);
//        }
//    }

    private function updateExistingBooking($booking, $bookingData)
    {
        DB::beginTransaction();

        try {
            $passengers    = $bookingData['passengers']     ?? [];
            $flightTickets = $bookingData['flight_tickets'] ?? [];

            // ── Active vs Exchanged ticket আলাদা করো ──
            $activeTickets    = collect($flightTickets)->filter(fn($t) => !($t['all_exchanged'] ?? false));
            $exchangedNumbers = collect($flightTickets)
                ->filter(fn($t) => $t['all_exchanged'] ?? false)
                ->pluck('number')
                ->filter()
                ->values()
                ->all();

            // ── Step 1: Exchanged ticket গুলো → status = reissued ──
            if (!empty($exchangedNumbers)) {
                BookingPassenger::where('booking_id', $booking->id)
                    ->whereIn('ticket_number', $exchangedNumbers)
                    ->update(['status' => 'reissued']);
            }

            // ── Step 2: DB থেকে reissued ছাড়া বাকি passengers নাও ──
            $eligiblePassengers = BookingPassenger::where('booking_id', $booking->id)
                ->where(fn($q) => $q
                    ->where('status', '!=', 'reissued')
                    ->orWhereNull('status')
                )
                ->orderBy('id')
                ->get();

            // ── Title remove helper — শুধু শেষের title বাদ দেবে ──
            $removeTrailingTitle = function(string $name): string {
                // শুধু string এর শেষে থাকা title বাদ দেবে (MR, MRS, MS, MISS, MSTR, DR, PROF)
                $cleaned = preg_replace('/\s+(mr|mrs|ms|miss|mstr|dr|prof)\.?\s*$/i', '', trim($name));
                return trim(preg_replace('/\s+/', ' ', $cleaned));
            };

            $updatedTickets = [];

            // ── Step 3: Active ticket গুলো → match করে update ──
            foreach ($activeTickets as $ticket) {
                $travelerIdx  = $ticket['traveler_index'] ?? null;
                $ticketNumber = $ticket['number']         ?? null;
                $ticketDate   = $ticket['date']           ?? null;
                $ticketStatus = $ticket['ticket_status']  ?? 'Issued';

                if (!$ticketNumber || !$travelerIdx) continue;

                $paxData     = $passengers[$travelerIdx - 1] ?? null;
                $passportNum = $paxData['passport_number']   ?? null;

                // GDS first_name এর শেষের title বাদ দাও
                $gdsFirstName = strtolower($removeTrailingTitle($paxData['first_name'] ?? ''));
                $gdsLastName  = strtolower(trim($paxData['last_name'] ?? ''));
                $gdsFullName  = trim($gdsFirstName . ' ' . $gdsLastName);
                $gdsDob       = $paxData['dob'] ?? null;

                $bookingPassenger = null;
                $matchStrategy    = 'none';

                // ── Strategy 1: Passport number match ──
                if ($passportNum) {
                    $bookingPassenger = $eligiblePassengers->first(
                        fn($bp) => trim($bp->passport_number ?? '') === trim($passportNum)
                    );
                    if ($bookingPassenger) $matchStrategy = 'passport';
                }

                // ── Strategy 2: First name (partial) + Last name match ──
                if (!$bookingPassenger && $gdsFullName) {
                    $bookingPassenger = $eligiblePassengers->first(function ($bp) use (
                        $gdsFirstName, $gdsLastName, $removeTrailingTitle
                    ) {
                        // DB এর নাম lowercase করো
                        $dbFirst = strtolower($removeTrailingTitle($bp->first_name ?? ''));
                        $dbLast  = strtolower(trim($bp->last_name ?? ''));

                        // First name এর meaningful parts (3+ char) এর যেকোনো একটা match
                        $firstParts  = array_filter(
                            explode(' ', $gdsFirstName),
                            fn($p) => strlen($p) > 2
                        );
                        $firstMatch = false;
                        foreach ($firstParts as $part) {
                            if (str_contains($dbFirst, $part)) {
                                $firstMatch = true;
                                break;
                            }
                        }

                        // Last name match
                        $lastMatch = !empty($gdsLastName) && str_contains($dbLast, $gdsLastName);

                        return $firstMatch && $lastMatch;
                    });
                    if ($bookingPassenger) $matchStrategy = 'name';
                }

                // ── Strategy 3: Last name + DOB match ──
                if (!$bookingPassenger && $gdsDob && $gdsLastName) {
                    $bookingPassenger = $eligiblePassengers->first(function ($bp) use (
                        $gdsLastName, $gdsDob, $removeTrailingTitle
                    ) {
                        $dbLast    = strtolower(trim($bp->last_name ?? ''));
                        $dbDob     = $bp->dob
                            ? \Carbon\Carbon::parse($bp->dob)->format('Y-m-d')
                            : null;
                        $gdsDobFmt = \Carbon\Carbon::parse($gdsDob)->format('Y-m-d');

                        return str_contains($dbLast, $gdsLastName) && $dbDob === $gdsDobFmt;
                    });
                    if ($bookingPassenger) $matchStrategy = 'lastname+dob';
                }

                // ── Strategy 4: Position fallback ──
                if (!$bookingPassenger) {
                    $bookingPassenger = $eligiblePassengers->values()->get($travelerIdx - 1);
                    if ($bookingPassenger) $matchStrategy = 'position';
                }

                if (!$bookingPassenger) {
                    \Log::warning('⚠️ No passenger found for ticket', [
                        'traveler_index' => $travelerIdx,
                        'ticket'         => $ticketNumber,
                        'gds_name'       => $gdsFullName,
                    ]);
                    continue;
                }

                $bookingPassenger->update([
                    'ticket_number'    => $ticketNumber,
                    'ticket_issued_at' => $ticketDate ? \Carbon\Carbon::parse($ticketDate) : now(),
                    'status'           => $ticketStatus,
                ]);

                $updatedTickets[] = $ticketNumber;

//                \Log::info('✅ Passenger ticket updated', [
//                    'passenger_id' => $bookingPassenger->id,
//                    'db_name'      => trim(($bookingPassenger->first_name ?? '') . ' ' . ($bookingPassenger->last_name ?? '')),
//                    'gds_name'     => $gdsFullName,
//                    'ticket'       => $ticketNumber,
//                    'strategy'     => $matchStrategy,
//                ]);
            }

            // ── Step 4: Booking update ──
            $allActiveNumbers = $activeTickets->pluck('number')->filter()->values()->all();

            $ticketStatuses = $activeTickets->pluck('ticket_status')
                ->map(fn($s) => strtolower($s ?? ''))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $bookingStatus = count($ticketStatuses) === 1
                ? $ticketStatuses[0]
                : (empty($ticketStatuses) ? $booking->status : 'partial');

            $latestTicketDate = $activeTickets
                ->pluck('date')
                ->filter()
                ->map(fn($d) => \Carbon\Carbon::parse($d))
                ->sortDesc()
                ->first();

            $booking->update([
                'ticket_number'    => json_encode($allActiveNumbers),
                'is_paid'          => count($allActiveNumbers) > 0 ? 1 : 0,
                'status'           => $bookingStatus,
                'ticket_issued_at' => $latestTicketDate ?? (count($allActiveNumbers) > 0 ? now() : null),
                'issued_by'        => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($updatedTickets) . ' ticket(s) updated successfully!',
                'data'    => [
                    'booking_id'       => $booking->id,
                    'status'           => $bookingStatus,
                    'active_tickets'   => $allActiveNumbers,
                    'reissued_tickets' => $exchangedNumbers,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function createNewBookingFromPNR($pnr, $source, $bookingData)
    {
        DB::beginTransaction();

        try {
            $user          = auth()->user();
            $passengers    = $bookingData['passengers']     ?? [];
            $segments      = $bookingData['segments']       ?? [];
            $flightTickets = $bookingData['flight_tickets'] ?? [];
            $journeys      = $bookingData['journeys']       ?? [];
            $pricing       = $bookingData['pricing']        ?? [];
            $grandTotal    = $pricing['grand_total']        ?? [];
            $fareBreakdowns= $pricing['fare_breakdowns']    ?? [];
            $provRes       = $bookingData['provider_reservation'] ?? [];
            $supplier      = $bookingData['supplier_locator']     ?? [];
            $actionStatus  = $bookingData['action_status']        ?? [];
            $contactInfo   = $bookingData['contact_info']         ?? [];

            // ── Pricing ──
            $fareData   = $fareBreakdowns[0]          ?? [];
            $totalAmount= $grandTotal['total']        ?? 0;
            $subtotal   = $grandTotal['subtotal']     ?? 0;
            $taxes      = $grandTotal['taxes']        ?? 0;
            $currency   = $grandTotal['currency']     ?? 'BDT';

            // ── Trip type ──
            $isRoundTrip = count($journeys) > 1;
            $flightType  = $isRoundTrip ? 'round_trip' : 'one_way';

            // ── Route ──
            $firstSeg = $segments[0]   ?? [];
            $lastSeg  = end($segments) ?: [];

            // ── Tickets ──
            $ticketNumbers  = array_column($flightTickets, 'number');
            $ticketStatuses = array_map(fn($t) => strtolower($t['ticket_status'] ?? 'issued'), $flightTickets);
            $unique         = array_unique(array_filter($ticketStatuses));
            $bookingStatus  = count($unique) === 1 ? $unique[0] : (count($unique) > 1 ? 'partial' : 'pending');

            // ── Passenger counts ──
            $adultCount  = collect($passengers)->where('traveler_type', 'ADT')->count();
            $childCount  = collect($passengers)->where('traveler_type', 'CNN')->count();
            $infantCount = collect($passengers)->where('traveler_type', 'INF')->count();

            // ── Contact ──
            $email = $contactInfo['emails'][0] ?? ($user->email ?? '');
            $phone = $contactInfo['phones'][0] ?? '';
            if (is_array($phone)) $phone = $phone['number'] ?? '';

            // ── Create Booking ──
            $booking = Booking::create([
                'code'             => $this->generateBookingCode(),
                'pnr_id'           => $pnr,
                'airline_pnr'      => $supplier['locator_code']  ?? null,
                'universal_id'     => $bookingData['universal_record']['locator_code'] ?? null,
                'pnr_raw_data'     => json_encode($bookingData),
                'flight_raw_data'  => json_encode($bookingData),
                'customer_id'      => $user->id,
                'create_user'      => $user->id,
                'object_model'     => 'flight',
                'source'           => $source,

                // Contact
                'email'            => $email,
                'phone'            => $phone,
                'first_name'       => $user->first_name ?? '',
                'last_name'        => $user->last_name  ?? '',
                'country'          => $user->country    ?? 'BD',

                // Status
                'status'           => $bookingStatus,
                'is_paid'          => count($ticketNumbers) > 0 ? 1 : 0,

                // Pricing
                'base_fee'         => $subtotal,
                'total_fee'        => $taxes,
                'total'            => $totalAmount,
                'currency'         => $currency,

                // Flight Details
                'flight_type'      => $flightType,
                'is_round_trip'    => $isRoundTrip,
                'is_refundable'    => $bookingData['is_cancelable'] ?? false,
                'seat_class'       => $firstSeg['cabin_class']  ?? 'Economy',
                'airline'          => $firstSeg['airline_name'] ?? null,

                // Route
                'flight_from'      => $firstSeg['origin']      ?? null,
                'flight_to'        => $lastSeg['destination']  ?? null,
                'start_date'       => $bookingData['start_date'] ?? now(),
                'end_date'         => $bookingData['end_date']   ?? now(),

                // Passengers
                'total_guests'     => count($passengers),
                'adult_count'      => $adultCount,
                'child_count'      => $childCount,
                'infant_count'     => $infantCount,

                // Tickets
                'ticket_number'    => json_encode($ticketNumbers),
                'ticket_issued_at' => count($ticketNumbers) > 0 ? now() : null,
                'ticket_deadline'  => $actionStatus['ticket_date'] ?? null,

                'booking_date'     => $provRes['host_create_date'] ?? now(),
                'created_at'       => now(),
            ]);

            // ── Create Passengers ──
            foreach ($passengers as $index => $pax) {
                $travelerIndex = $index + 1;

                $ticket = collect($flightTickets)->first(fn($t) =>
                    ($t['traveler_index'] ?? null) == $travelerIndex
                );

                $passportDoc = collect($pax['identity_documents'] ?? [])
                    ->first(fn($d) => ($d['documentType'] ?? '') === 'PASSPORT');

                $passengerFare = count($passengers) > 0 ? $totalAmount / count($passengers) : 0;
                $passengerBase = count($passengers) > 0 ? $subtotal   / count($passengers) : 0;
                $passengerTax  = count($passengers) > 0 ? $taxes      / count($passengers) : 0;

                $fareCons = $fareData['fare_construction'][0] ?? [];

                BookingPassenger::create([
                    'booking_id'           => $booking->id,

                    // Type
                    'traveler_type'        => strtolower($pax['passenger_type'] ?? 'adult'),
                    'title'                => $this->getTitleFromName($pax['first_name'] ?? ''),
                    'seat_type'            => strtolower($pax['passenger_type'] ?? 'adult'),
                    'passenger_type_code'  => $pax['traveler_type'] ?? 'ADT',

                    // Personal
                    'first_name'           => $pax['first_name']  ?? '',
                    'last_name'            => $pax['last_name']   ?? '',
                    'email'                => $pax['email']       ?? $email,
                    'phone'                => $pax['phone']       ?? $phone,
                    'gender'               => strtolower($pax['gender'] ?? 'male'),
                    'dob'                  => $pax['dob']         ?? ($passportDoc['birthDate'] ?? null),

                    // Passport
                    'passport_number'      => $pax['passport_number']  ?? ($passportDoc['documentNumber']    ?? null),
                    'passport_expiry_date' => $pax['passport_expiry']  ?? ($passportDoc['expiryDate']        ?? null),
                    'document_type'        => 'passport',
                    'country'              => $pax['passport_country'] ?? ($passportDoc['issuingCountryCode'] ?? 'BD'),
                    'issuing_location'     => $pax['passport_country'] ?? ($passportDoc['issuingCountryCode'] ?? 'BD'),

                    // Ticket
                    'ticket_number'        => $ticket['number']        ?? null,
                    'ticket_issued_at'     => $ticket
                        ? ($ticket['date'] ? \Carbon\Carbon::parse($ticket['date']) : now())
                        : null,
                    'status'               => $ticket['ticket_status'] ?? null,

                    // Pricing
                    'total'                => $passengerFare,
                    'base'                 => $passengerBase,
                    'price'                => $passengerFare,
                    'tax'                  => $passengerTax,
                    'currency'             => $currency,

                    // Baggage
                    'included_checked_bags'      => $pricing['checked_bag_kg'] ?? 0,
                    'included_checked_bags_unit' => 'KG',

                    // Cabin
                    'cabin'                => $firstSeg['cabin_class']       ?? 'Economy',
                    'class'                => $firstSeg['class_of_service']  ?? 'Y',
                    'fare_basis'           => $fareCons['fare_basis']        ?? null,

                    'object_model'         => 'flight',
                    'create_user'          => $user->id,
                    'created_at'           => now(),
                ]);
            }

            // ── Create Routes (Segments) ──
            foreach ($segments as $seg) {
                BookingRoute::create([
                    'booking_id'       => $booking->id,

                    // Departure
                    'departure_iata_code' => $seg['origin']             ?? null,
                    'departure_terminal'  => $seg['departure_terminal'] ?? null,
                    'departure_at'        => $seg['departure_time']     ?? null,

                    // Arrival
                    'arrival_iata_code'   => $seg['destination']       ?? null,
                    'arrival_terminal'    => $seg['arrival_terminal']  ?? null,
                    'arrival_at'          => $seg['arrival_time']      ?? null,

                    // Flight
                    'carrier_code'        => $seg['carrier']           ?? null,
                    'aircraft_code'       => $seg['equipment']         ?? null,
                    'flight_number'       => ($seg['carrier'] ?? '') . ($seg['flight_number'] ?? ''),
                    'duration'            => ($seg['travel_time'] ?? 0) . ' min',

                    // Cabin
                    'cabin'               => $seg['cabin_class']       ?? 'Economy',
                    'class'               => $seg['class_of_service']  ?? 'Y',

                    // Meta
                    'meta'                => json_encode([
                        'confirmation_id'   => $seg['confirmation_id']          ?? null,
                        'airline_name'      => $seg['airline_name']             ?? null,
                        'operating_airline' => $seg['operating_airline_name']   ?? null,
                        'aircraft_name'     => $seg['aircraft_name']            ?? null,
                        'meals'             => $seg['meals']                    ?? [],
                        'status'            => $seg['status_name']              ?? 'Confirmed',
                        'distance'          => $seg['distance_miles']           ?? 0,
                        'sell_messages'     => $seg['sell_messages']            ?? [],
                    ]),

                    'create_user'         => $user->id,
                    'created_at'          => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success'      => true,
                'message'      => 'Booking created with ' . count($ticketNumbers) . ' ticket(s)!',
                'booking_id'   => $booking->id,
                'booking_code' => $booking->code,
                'ticket_count' => count($ticketNumbers),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function generateBookingCode()
    {
        return 'BK' . strtoupper(substr(uniqid(), -6));
    }

    private function getTitleFromName($name)
    {
        $name = strtoupper($name);
        if (strpos($name, 'MR') !== false) return 'Mr';
        if (strpos($name, 'MRS') !== false) return 'Mrs';
        if (strpos($name, 'MS') !== false) return 'Ms';
        if (strpos($name, 'MISS') !== false) return 'Miss';
        if (strpos($name, 'MASTER') !== false) return 'Master';
        return 'Mr';
    }

}
