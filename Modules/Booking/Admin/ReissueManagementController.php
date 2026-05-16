<?php
namespace Modules\Booking\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Modules\Booking\Models\BookingPassenger;
use Modules\Booking\Models\BookingReissue;
use Modules\Booking\Models\Booking;
use Modules\User\Models\Wallet\Transaction;

class ReissueManagementController extends Controller
{

    public function index(Request $request)
    {
        $rows = BookingReissue::with(['booking', 'booking.user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Booking::admin.reissues.index', compact('rows'));
    }
    public function show($id)
    {
        $reissue = BookingReissue::with(['booking', 'passengers'])->findOrFail($id);
        return view('Booking::admin.reissues.show', compact('reissue'));
    }

    public function setAmount(Request $request, $id)
    {
        Log::info('Reissue Set Amount Request', [
            'reissue_id'   => $id,
            'request_data' => $request->all(),
            'user_id'      => auth()->id()
        ]);

        try {
            $request->validate([
                'passenger_details'                  => 'required|array|min:1',
                'passenger_details.*.passenger_id'   => 'required|integer',
                'passenger_details.*.old_fare'       => 'required|numeric|min:0',
                'passenger_details.*.new_fare'       => 'required|numeric|min:0',
                'passenger_details.*.reissue_charge' => 'required|numeric|min:0',
                'passenger_details.*.service_charge' => 'nullable|numeric|min:0',
                'total_old_fare'                     => 'required|numeric|min:0',
                'total_new_fare'                     => 'required|numeric|min:0',
                'total_reissue_charges'              => 'required|numeric|min:0',
                'total_service_charges'              => 'nullable|numeric|min:0',
                'fare_difference'                    => 'required|numeric',
                'total_extra'                        => 'required|numeric|min:0',
            ]);

            $reissue = BookingReissue::findOrFail($id);

            if (!in_array($reissue->status, ['pending', 'selected'])) {
                return response()->json([
                    'status'  => false,
                    'message' => 'This reissue request is no longer pending.'
                ], 400);
            }

            DB::beginTransaction();

            // =====================================================
            // UPDATE PASSENGERS
            // =====================================================
            $updatedPassengers  = [];
            $notFoundPassengers = [];

            foreach ($request->passenger_details as $detail) {
                $passenger = BookingPassenger::find($detail['passenger_id']);

                if (!$passenger) {
                    $notFoundPassengers[] = $detail['passenger_id'];
                    continue;
                }

//                $passenger->update([
//                    'total' => $detail['new_fare'],
//                ]);

                $updatedPassengers[] = [
                    'passenger_id'   => $passenger->id,
                    'name'           => $passenger->first_name . ' ' . $passenger->last_name,
                    'old_fare'       => $detail['old_fare'],
                    'new_fare'       => $detail['new_fare'],
                    'reissue_charge' => $detail['reissue_charge'] ?? 0,
                    'service_charge' => $detail['service_charge'] ?? 0,
                ];

                Log::info('Passenger Updated', [
                    'passenger_id'   => $passenger->id,
                    'name'           => $passenger->first_name . ' ' . $passenger->last_name,
                    'old_fare'       => $detail['old_fare'],
                    'new_fare'       => $detail['new_fare'],
                    'reissue_charge' => $detail['reissue_charge'] ?? 0,
                    'service_charge' => $detail['service_charge'] ?? 0,
                ]);
            }

            if (!empty($notFoundPassengers)) {
                DB::rollBack();
                return response()->json([
                    'status'  => false,
                    'message' => 'Passenger(s) not found: ' . implode(', ', $notFoundPassengers)
                ], 400);
            }

            if (empty($updatedPassengers)) {
                DB::rollBack();
                return response()->json([
                    'status'  => false,
                    'message' => 'No passengers were updated. Please check passenger data.'
                ], 400);
            }

            // =====================================================
            // BUILD FULL JSON — পুরো request data save করো
            // =====================================================
            $fareDetailsJson = json_encode([
                'new_pnr'               => $request->new_pnr,
                'airline_response'      => $request->airline_response,
                'passenger_details'     => $request->passenger_details,
                'total_old_fare'        => (float)$request->total_old_fare,
                'total_new_fare'        => (float)$request->total_new_fare,
                'total_reissue_charges' => (float)$request->total_reissue_charges,
                'total_service_charges' => (float)($request->total_service_charges ?? 0),
                'fare_difference'       => (float)$request->fare_difference,
                'total_extra'           => (float)$request->total_extra,
                'set_by'                => auth()->id(),
                'set_at'                => now()->toDateTimeString(),
            ]);

            // =====================================================
            // UPDATE REISSUE RECORD
            // =====================================================
            $reissue->update([
                'fare_difference'       => (float)$request->fare_difference,
                'reissue_charges'       => (float)$request->total_reissue_charges,
                'service_charge'        => (float)($request->total_service_charges ?? 0),
                'total_amount'          => (float)$request->total_extra,
                'new_pnr'               => $request->new_pnr,
                'airline_response'      => $request->airline_response,
                'passenger_fare_details'=> $fareDetailsJson,
                'status'                => 'waiting_user_approval',
                'processed_by'          => auth()->id(),
            ]);

            DB::commit();

            Log::info('Reissue Amount Set Successfully', [
                'reissue_id'       => $reissue->id,
                'fare_difference'  => $request->fare_difference,
                'reissue_charges'  => $request->total_reissue_charges,
                'service_charges'  => $request->total_service_charges,
                'total_extra'      => $request->total_extra,
                'updated_passengers' => count($updatedPassengers),
                'passenger_fare_details' => $fareDetailsJson,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Reissue amount set successfully. ' .
                    count($updatedPassengers) .
                    ' passenger(s) updated. Waiting for user approval.'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reissue Set Amount Error', [
                'reissue_id' => $id,
                'error'      => $e->getMessage(),
                'line'       => $e->getLine()
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Step 2: Final approve after user has approved
     */
    public function approve(Request $request, $id)
    {
        Log::info('Reissue Final Approve Request', [
            'reissue_id'     => $id,
            'ticket_numbers' => $request->ticket_numbers,
            'user_id'        => auth()->id()
        ]);

        try {
            $request->validate([
                'ticket_numbers'                 => 'required|array|min:1',
                'ticket_numbers.*.passenger_id'  => 'required|integer',
                'ticket_numbers.*.ticket_number' => 'required|string',
            ]);

            $reissue    = BookingReissue::with(['booking.user'])->findOrFail($id);
            $oldBooking = $reissue->booking;
            $user       = $oldBooking->user;

            if ($reissue->status !== 'user_approved') {
                return response()->json([
                    'status'  => false,
                    'message' => 'User has not approved this reissue yet.'
                ], 400);
            }

            if (!$oldBooking) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Old booking not found.'
                ], 400);
            }

            if (!$user) {
                return response()->json([
                    'status'  => false,
                    'message' => 'User not found.'
                ], 400);
            }

            // ── Saved data from setAmount ──────────────────────────
            $savedData           = json_decode($reissue->passenger_fare_details, true);
            $paxDetails          = $savedData['passenger_details']      ?? [];
            $totalExtra          = (float)($savedData['total_extra']           ?? $reissue->total_amount);
            $fareDifference      = (float)($savedData['fare_difference']       ?? $reissue->fare_difference);
            $totalReissueCharges = (float)($savedData['total_reissue_charges'] ?? $reissue->reissue_charges);
            $totalServiceCharges = (float)($savedData['total_service_charges'] ?? 0);
            $newPnr              = $reissue->new_pnr;

            // Ticket numbers keyed by passenger_id
            $ticketMap = [];
            foreach ($request->ticket_numbers as $t) {
                $ticketMap[(int)$t['passenger_id']] = $t['ticket_number'];
            }

            // Passenger IDs
            $passengerIds = is_array($reissue->passenger_ids)
                ? $reissue->passenger_ids
                : json_decode($reissue->passenger_ids, true);

            $oldPassengers = BookingPassenger::whereIn('id', $passengerIds)->get();

            // ── Wallet check ───────────────────────────────────────
            $currentBalance = (float)$user->credit_balance;

            if ($currentBalance < $totalExtra) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Insufficient wallet balance. Required: BDT ' .
                        number_format($totalExtra, 2) .
                        ', Available: BDT ' . number_format($currentBalance, 2)
                ], 400);
            }

            DB::beginTransaction();

            // ══════════════════════════════════════════════════════
            // CASE 1: No New PNR — same booking
            // ══════════════════════════════════════════════════════
            if (empty($newPnr)) {

                // Old booking void_charge += totalExtra
                $currentVoidCharge = (float)($oldBooking->void_charge ?? 0);
                $oldBooking->update([
                    'void_charge' => $currentVoidCharge + $totalExtra
                ]);

                foreach ($oldPassengers as $oldPax) {
                    $paxFare        = collect($paxDetails)->firstWhere('passenger_id', $oldPax->id);
                    $fareDiff       = $paxFare ? ($paxFare['new_fare'] - $paxFare['old_fare']) : 0;
                    $reissueCharge  = (float)($paxFare['reissue_charge'] ?? 0);
                    $serviceCharge  = (float)($paxFare['service_charge'] ?? 0);
                    $passengerExtra = $fareDiff + $reissueCharge + $serviceCharge;
                    $ticketNumber   = $ticketMap[$oldPax->id] ?? null;

                    // Mark old passenger reissued
                    $oldPax->update(['status' => 'reissued']);

                    // Duplicate passenger — same booking
                    $newPax                    = $oldPax->replicate();
                    $newPax->status            = 'issued';
                    $newPax->price             = $passengerExtra;
                    $newPax->total             = $passengerExtra;
                    $newPax->base              = $fareDiff + $reissueCharge;
                    $newPax->service_charge    = $serviceCharge;
                    $newPax->ticket_number     = $ticketNumber;
                    $newPax->ticket_issued_at  = now();
                    $newPax->pnr               = $oldPax->pnr;

                    // Zero out financial fields
                    $newPax->tax               = 0;
                    $newPax->gross_fare        = 0;
                    $newPax->ait_amount        = 0;
                    $newPax->own_discount      = 0;
                    $newPax->own_seg_discount  = 0;
                    $newPax->commission        = 0;
                    $newPax->own_cost          = 0;
                    $newPax->user_discount     = 0;
                    $newPax->user_seg_discount = 0;
                    $newPax->cost_amount       = 0;

                    // Calculated fields
                    $newPax->user_payable      = $passengerExtra;
                    $newPax->profit            = $serviceCharge;

                    $newPax->meta              = json_encode([
                        'reissued_from_passenger_id' => $oldPax->id,
                        'old_pnr'                    => $oldPax->pnr,
                        'old_ticket_number'          => $oldPax->ticket_number,
                        'reissue_id'                 => $reissue->id,
                    ]);
                    $newPax->created_at        = now();
                    $newPax->updated_at        = now();
                    $newPax->save();

                    Log::info('Passenger Duplicated (Case 1 - Same Booking)', [
                        'old_passenger_id' => $oldPax->id,
                        'new_passenger_id' => $newPax->id,
                        'ticket_number'    => $ticketNumber,
                        'extra'            => $passengerExtra,
                        'profit'           => $serviceCharge,
                    ]);
                }

                // Old booking status update
                $totalPaxCount    = BookingPassenger::where('booking_id', $oldBooking->id)->count();
                $reissuedPaxCount = BookingPassenger::where('booking_id', $oldBooking->id)
                    ->where('status', 'reissued')->count();

                $oldBooking->update([
                    'status' => ($reissuedPaxCount < $totalPaxCount) ? 'partial_reissued' : 'reissued'
                ]);

                // ══════════════════════════════════════════════════════
                // CASE 2: New PNR — create new booking
                // ══════════════════════════════════════════════════════
            } else {

                // New booking create — meta unset (column নেই)
                $newBooking = $oldBooking->replicate();
                unset($newBooking->meta);

                $newBooking->code           = 'RBK' . strtoupper(uniqid());
                $newBooking->status         = 'issued';
                $newBooking->pnr_id         = $newPnr;
                $newBooking->pnr_raw_data   = null;
                $newBooking->total          = $totalExtra;
                $newBooking->base_fee       = $fareDifference + $totalReissueCharges;
                $newBooking->total_fee      = $fareDifference + $totalReissueCharges;
                $newBooking->paid           = $totalExtra;
                $newBooking->total_before_discount           = 0;
                $newBooking->supplier_fee           = 0;
                $newBooking->ticketing_fee           = $totalServiceCharges;
                $newBooking->pay_now        = 0;
                $newBooking->void_charge    = 0;
                $newBooking->booking_date   = now();
                $newBooking->confirmed_at   = now();
                $newBooking->paid_at        = now();
                $newBooking->reissue_id     = $reissue->id;
                $newBooking->created_at     = now();
                $newBooking->updated_at     = now();

                // All ticket numbers array
                $allTickets                = array_values($ticketMap);
                $newBooking->ticket_number = json_encode($allTickets);

                $newBooking->save();

                Log::info('New Booking Created (Case 2)', [
                    'new_booking_id'   => $newBooking->id,
                    'new_booking_code' => $newBooking->code,
                    'total_extra'      => $totalExtra,
                ]);

                // Duplicate passengers into new booking
                foreach ($oldPassengers as $oldPax) {
                    $paxFare        = collect($paxDetails)->firstWhere('passenger_id', $oldPax->id);
                    $fareDiff       = $paxFare ? ($paxFare['new_fare'] - $paxFare['old_fare']) : 0;
                    $reissueCharge  = (float)($paxFare['reissue_charge'] ?? 0);
                    $serviceCharge  = (float)($paxFare['service_charge'] ?? 0);
                    $passengerExtra = $fareDiff + $reissueCharge + $serviceCharge;
                    $ticketNumber   = $ticketMap[$oldPax->id] ?? null;

                    // Mark old passenger reissued
                    $oldPax->update(['status' => 'reissued']);

                    // Duplicate into new booking
                    $newPax                    = $oldPax->replicate();
                    $newPax->booking_id        = $newBooking->id;
                    $newPax->status            = 'issued';
                    $newPax->price             = $passengerExtra;
                    $newPax->total             = $passengerExtra;
                    $newPax->base              = $fareDiff + $reissueCharge;
                    $newPax->service_charge    = $serviceCharge;
                    $newPax->ticket_number     = $ticketNumber;
                    $newPax->ticket_issued_at  = now();
                    $newPax->pnr               = $newPnr;

                    // Zero out financial fields
                    $newPax->tax               = 0;
                    $newPax->gross_fare        = 0;
                    $newPax->ait_amount        = 0;
                    $newPax->own_discount      = 0;
                    $newPax->own_seg_discount  = 0;
                    $newPax->commission        = 0;
                    $newPax->own_cost          = 0;
                    $newPax->user_discount     = 0;
                    $newPax->user_seg_discount = 0;
                    $newPax->cost_amount       = 0;

                    // Calculated fields
                    $newPax->user_payable      = $passengerExtra;
                    $newPax->profit            = $serviceCharge;

                    $newPax->meta              = json_encode([
                        'reissued_from_passenger_id' => $oldPax->id,
                        'old_pnr'                    => $oldPax->pnr,
                        'old_ticket_number'          => $oldPax->ticket_number,
                        'reissue_id'                 => $reissue->id,
                    ]);
                    $newPax->created_at        = now();
                    $newPax->updated_at        = now();
                    $newPax->save();

                    Log::info('Passenger Duplicated (Case 2 - New Booking)', [
                        'old_passenger_id' => $oldPax->id,
                        'new_passenger_id' => $newPax->id,
                        'new_booking_id'   => $newBooking->id,
                        'ticket_number'    => $ticketNumber,
                        'profit'           => $serviceCharge,
                    ]);
                }

                // Old booking → partial_reissued
                $oldBooking->update(['status' => 'partial_reissued']);

                // Update reissue with new booking id
                $reissue->new_booking_id = $newBooking->id;
            }

            // ── Wallet Transaction ─────────────────────────────────
            $user->decrement('credit_balance', $totalExtra);

            Transaction::create([
                'user_id'          => $user->id,
                'booking_id'       => $oldBooking->id,
                'ref_id'           => $reissue->id,
                'type'             => 'debit',
                'transaction_type' => 'reissue_payment',
                'amount'           => $totalExtra,
                'status'           => 'payment',
                'reference'        => 'Reissue Payment - Reissue #' . $reissue->id,
                'remarks'          => 'Fare diff: BDT ' . number_format($fareDifference, 2) .
                    ', Reissue charges: BDT ' . number_format($totalReissueCharges, 2) .
                    ', Service charges: BDT ' . number_format($totalServiceCharges, 2) .
                    ', Total: BDT ' . number_format($totalExtra, 2),
                'meta'             => json_encode([
                    'balance_before'        => $currentBalance,
                    'balance_after'         => $currentBalance - $totalExtra,
                    'old_booking_id'        => $oldBooking->id,
                    'reissue_id'            => $reissue->id,
                    'fare_difference'       => $fareDifference,
                    'total_reissue_charges' => $totalReissueCharges,
                    'total_service_charges' => $totalServiceCharges,
                    'total_extra'           => $totalExtra,
                ]),
                'create_user'  => auth()->id(),
                'created_at'   => now(),
                'deposit_date' => now(),
            ]);

            Log::info('Wallet Debited', [
                'user_id'        => $user->id,
                'amount'         => $totalExtra,
                'balance_before' => $currentBalance,
                'balance_after'  => $currentBalance - $totalExtra,
            ]);

            // ── Finalize Reissue ───────────────────────────────────
            $reissue->update([
                'status'       => 'completed',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            DB::commit();

            // ── Response ───────────────────────────────────────────
            $message  = "Reissue approved successfully!\n\n";
            $message .= "Fare Difference: BDT " . number_format($fareDifference, 2) . "\n";
            $message .= "Reissue Charges: BDT " . number_format($totalReissueCharges, 2) . "\n";
            $message .= "Service Charges: BDT " . number_format($totalServiceCharges, 2) . "\n";
            $message .= "Total Deducted from Wallet: BDT " . number_format($totalExtra, 2);

            return response()->json(['status' => true, 'message' => $message]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reissue Approve Error', [
                'reissue_id' => $id,
                'error'      => $e->getMessage(),
                'line'       => $e->getLine(),
                'file'       => $e->getFile()
            ]);
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Step 1: Admin sets amount and sends to user for approval
     */
//    public function setAmount(Request $request, $id)
//    {
//        Log::info('Reissue Set Amount Request', [
//            'reissue_id' => $id,
//            'request_data' => $request->all(),
//            'user_id' => auth()->id()
//        ]);
//
//        try {
//            $request->validate([
//                'passenger_details' => 'required|array|min:1',
//                'passenger_details.*.passenger_id' => 'required|integer',
//                'passenger_details.*.old_fare' => 'required|numeric|min:0',
//                'passenger_details.*.new_fare' => 'required|numeric|min:0',
//                'passenger_details.*.reissue_charge' => 'required|numeric|min:0',
//                'total_old_fare' => 'required|numeric|min:0',
//                'total_new_fare' => 'required|numeric|min:0',
//                'total_reissue_charges' => 'required|numeric|min:0',
//            ]);
//
//            $reissue = BookingReissue::findOrFail($id);
//
//            if ($reissue->status !== 'pending') {
//                return response()->json([
//                    'status' => false,
//                    'message' => 'This reissue request is no longer pending.'
//                ], 400);
//            }
//
//            $totalFareDifference = $request->total_new_fare - $request->total_old_fare;
//            $grandTotal = $totalFareDifference + $request->total_reissue_charges;
//
//            $reissue->update([
//                'fare_difference' => $totalFareDifference,
//                'reissue_charges' => $request->total_reissue_charges,
//                'total_amount' => $grandTotal,
//                'new_pnr' => $request->new_pnr,
//                'airline_response' => $request->airline_response,
//                'passenger_fare_details' => json_encode($request->passenger_details),
//                'status' => 'waiting_user_approval',
//                'added_by' => auth()->id(),
//            ]);
//
//            // TODO: Send notification to user
//            // $this->sendUserNotification($reissue, 'amount_set');
//
//            Log::info('Reissue Amount Set Successfully', [
//                'reissue_id' => $reissue->id,
//                'fare_difference' => $totalFareDifference,
//                'reissue_charges' => $request->total_reissue_charges,
//                'total_amount' => $grandTotal,
//                'status' => 'waiting_user_approval'
//            ]);
//
//            return response()->json([
//                'status' => true,
//                'message' => 'Reissue amount set successfully. Waiting for user approval. Customer to pay: BDT ' . number_format($grandTotal, 2)
//            ]);
//
//        } catch (\Illuminate\Validation\ValidationException $e) {
//            return response()->json([
//                'status' => false,
//                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
//            ], 422);
//
//        } catch (\Exception $e) {
//            Log::error('Reissue Set Amount Error', [
//                'reissue_id' => $id,
//                'error' => $e->getMessage()
//            ]);
//            return response()->json([
//                'status' => false,
//                'message' => 'Error: ' . $e->getMessage()
//            ], 500);
//        }
//    }
//
//    /**
//     * Step 2: Final approve after user has approved
//     */
//    public function approve(Request $request, $id)
//    {
//        Log::info('Reissue Final Approve Request', [
//            'reissue_id' => $id,
//            'user_id' => auth()->id()
//        ]);
//
//        try {
//            $reissue = BookingReissue::with(['booking.user'])->findOrFail($id);
//
//            $oldBooking = $reissue->booking;
////            return $oldBooking;
//            $newBooking = Booking::find($reissue->new_booking_id);
//            $user = $oldBooking->customer_id;
//
//            // Check if user has approved
//            if ($reissue->status !== 'user_approved') {
//                return response()->json([
//                    'status' => false,
//                    'message' => 'User has not approved this reissue yet.'
//                ], 400);
//            }
//
//            if (!$oldBooking) {
//                return response()->json([
//                    'status' => false,
//                    'message' => 'Old booking not found.'
//                ], 400);
//            }
//
//            if (!$newBooking) {
//                return response()->json([
//                    'status' => false,
//                    'message' => 'New booking not found.'
//                ], 400);
//            }
//
//            if (!$user) {
//                return response()->json([
//                    'status' => false,
//                    'message' => 'User not found.'
//                ], 400);
//            }
//
//            // Get passenger IDs
//            $passengerIds = is_array($reissue->passenger_ids)
//                ? $reissue->passenger_ids
//                : json_decode($reissue->passenger_ids, true);
//            $passengerCount = count($passengerIds);
//
//            // Get saved amounts
//            $transferAmount = 0;
//            $passengerDetails = json_decode($reissue->passenger_fare_details, true) ?? [];
//            return $passengerDetails;
//            foreach ($passengerDetails as $detail) {
//                $transferAmount += (float)($detail['old_fare'] ?? 0);
//            }
//
//            $reissueCharges = (float)$reissue->reissue_charges;
//            $newBookingTotal = (float)$newBooking->total;
//            $newBookingPaid = (float)$newBooking->paid;
//
//            // Total available = Transfer + Already paid
//            $totalAvailable = $transferAmount + $newBookingPaid;
//
//            // Total needed = New booking total + Reissue charges
//            $totalNeeded = $newBookingTotal + $reissueCharges;
//
//            // Final calculation
//            $finalDifference = $totalAvailable - $totalNeeded;
//
//            Log::info('Calculation', [
//                'transfer_amount' => $transferAmount,
//                'new_booking_total' => $newBookingTotal,
//                'new_booking_paid' => $newBookingPaid,
//                'reissue_charges' => $reissueCharges,
//                'total_available' => $totalAvailable,
//                'total_needed' => $totalNeeded,
//                'final_difference' => $finalDifference
//            ]);
//
//            DB::beginTransaction();
//
//            // Update Reissue Record
//            $reissue->update([
//                'status' => 'completed',
//                'processed_by' => auth()->id(),
//                'processed_at' => now()
//            ]);
//
//            // Update New Booking
//            $newBooking->update([
//                'paid' => $newBookingTotal,
//                'pay_now' => 0,
//                'status' => 'booked',
//            ]);
//
//            // Handle Wallet Transaction
//            $currentBalance = $user->credit_balance;
//
//            if ($finalDifference < 0) {
//                // User needs to pay (shortage)
//                $amountToPay = abs($finalDifference);
//
//                if ($currentBalance < $amountToPay) {
//                    DB::rollBack();
//                    return response()->json([
//                        'status' => false,
//                        'message' => "Insufficient balance. Required: BDT " . number_format($amountToPay, 2) . ", Available: BDT " . number_format($currentBalance, 2)
//                    ], 400);
//                }
//
//                // Deduct from wallet
//                $user->decrement('credit_balance', $amountToPay);
//
//                // Transaction record
//                Transaction::create([
//                    'user_id' => $user->id,
//                    'booking_id' => $newBooking->id,
//                    'ref_id' => $reissue->id,
//                    'type' => 'debit',
//                    'transaction_type' => 'reissue_payment',
//                    'amount' => $amountToPay,
//                    'status' => 'payment',
//                    'reference' => 'Reissue payment - Booking #' . $newBooking->id,
//                    'remarks' => 'Reissue payment for shortage amount',
//                    'meta' => json_encode([
//                        'balance_before' => $currentBalance,
//                        'balance_after' => $currentBalance - $amountToPay,
//                        'old_booking_id' => $oldBooking->id,
//                        'new_booking_id' => $newBooking->id,
//                        'reissue_id' => $reissue->id,
//                    ]),
//                    'create_user' => auth()->id(),
//                    'created_at' => now(),
//                    'deposit_date' => now(),
//                ]);
//
//                Log::info('Wallet Deducted', [
//                    'amount' => $amountToPay,
//                    'balance_before' => $currentBalance,
//                    'balance_after' => $user->credit_balance
//                ]);
//
//            } elseif ($finalDifference > 0) {
//                // User gets refund (excess)
//                $refundAmount = $finalDifference;
//
//                // Add to wallet
//                $user->increment('credit_balance', $refundAmount);
//
//                // Transaction record
//                Transaction::create([
//                    'user_id' => $user->id,
//                    'booking_id' => $newBooking->id,
//                    'ref_id' => $reissue->id,
//                    'type' => 'credit',
//                    'transaction_type' => 'reissue_refund',
//                    'amount' => $refundAmount,
//                    'status' => 'refund',
//                    'reference' => 'Reissue refund - Booking #' . $newBooking->id,
//                    'remarks' => 'Reissue refund for excess amount',
//                    'meta' => json_encode([
//                        'balance_before' => $currentBalance,
//                        'balance_after' => $currentBalance + $refundAmount,
//                        'old_booking_id' => $oldBooking->id,
//                        'new_booking_id' => $newBooking->id,
//                        'reissue_id' => $reissue->id,
//                    ]),
//                    'create_user' => auth()->id(),
//                    'created_at' => now(),
//                    'deposit_date' => now(),
//                ]);
//
//                Log::info('Wallet Credited', [
//                    'amount' => $refundAmount,
//                    'balance_before' => $currentBalance,
//                    'balance_after' => $user->credit_balance
//                ]);
//            }
//
//            // Update Old Booking Passengers Status
//            BookingPassenger::whereIn('id', $passengerIds)
//                ->where('booking_id', $oldBooking->id)
//                ->update(['status' => 'reissued']);
//
//            // Update Old Booking Status
//            $totalPassengers = BookingPassenger::where('booking_id', $oldBooking->id)->count();
//            $remainingPassengers = $totalPassengers - $passengerCount;
//
//            if ($remainingPassengers > 0) {
//                $oldBooking->update(['status' => 'partial_reissued']);
//            } else {
//                $oldBooking->update(['status' => 'reissued']);
//            }
//
//            DB::commit();
//
//            Log::info('Reissue Approved Successfully');
//
//            // Response message
//            $message = "Reissue approved successfully!\n";
//            $message .= "Old Booking: #{$oldBooking->id}\n";
//            $message .= "New Booking: #{$newBooking->id}\n";
//
//            if ($finalDifference < 0) {
//                $message .= "Paid from wallet: BDT " . number_format(abs($finalDifference), 2);
//            } elseif ($finalDifference > 0) {
//                $message .= "Refunded to wallet: BDT " . number_format($finalDifference, 2);
//            } else {
//                $message .= "No additional payment required";
//            }
//
//            return response()->json([
//                'status' => true,
//                'message' => $message
//            ]);
//
//        } catch (\Exception $e) {
//            DB::rollBack();
//            Log::error('Reissue Approve Error', [
//                'reissue_id' => $id,
//                'error' => $e->getMessage(),
//                'line' => $e->getLine()
//            ]);
//            return response()->json([
//                'status' => false,
//                'message' => 'Error: ' . $e->getMessage()
//            ], 500);
//        }
//    }


    /**
     * Step 2: Final approve after user has approved
     */
//    public function approve(Request $request, $id)
//    {
//        Log::info('Reissue Final Approve Request', [
//            'reissue_id' => $id,
//            'user_id' => auth()->id()
//        ]);
//
//        try {
//            $reissue = BookingReissue::with(['booking.user'])->findOrFail($id);
//            $oldBooking = $reissue->booking;
//            $newBooking = Booking::find($reissue->new_booking_id);
//            $user = $oldBooking->customer_id;
//
//            // Check if user has approved
//            if ($reissue->status !== 'user_approved') {
//                return response()->json([
//                    'status' => false,
//                    'message' => 'User has not approved this reissue yet.'
//                ], 400);
//            }
//
//            if (!$oldBooking) {
//                return response()->json([
//                    'status' => false,
//                    'message' => 'Old booking not found.'
//                ], 400);
//            }
//
//            if (!$newBooking) {
//                return response()->json([
//                    'status' => false,
//                    'message' => 'New booking not found.'
//                ], 400);
//            }
//
//            if (!$user) {
//                return response()->json([
//                    'status' => false,
//                    'message' => 'User not found.'
//                ], 400);
//            }
//
//            // Get passenger IDs
//            $passengerIds = is_array($reissue->passenger_ids)
//                ? $reissue->passenger_ids
//                : json_decode($reissue->passenger_ids, true);
//            $passengerCount = count($passengerIds ?? []);
//
//            // =====================================================
//            // CALCULATION
//            // =====================================================
//
//            $oldBookingPaid = (float) $oldBooking->paid;           // Old booking e customer koto diyechilo
//            $newBookingPaid = (float) $newBooking->paid;           // New booking e already koto paid (if any)
//            $newBookingTotal = (float) $newBooking->total;         // New booking er total price
//            $reissueCharges = (float) $reissue->reissue_charges;   // Reissue charge
//
//            // Total Available = Old booking paid + New booking paid
//            $totalAvailable = $oldBookingPaid + $newBookingPaid;
//
//            // Total Needed = New booking total + Reissue charges
//            $totalNeeded = $newBookingTotal + $reissueCharges;
//
//            // Difference calculation
//            $difference = $totalAvailable - $totalNeeded;
//
//            Log::info('Reissue Calculation', [
//                'old_booking_id' => $oldBooking->id,
//                'new_booking_id' => $newBooking->id,
//                'old_booking_paid' => $oldBookingPaid,
//                'new_booking_paid' => $newBookingPaid,
//                'new_booking_total' => $newBookingTotal,
//                'reissue_charges' => $reissueCharges,
//                'total_available' => $totalAvailable,
//                'total_needed' => $totalNeeded,
//                'difference' => $difference
//            ]);
//
//            $currentBalance = (float) $user->credit_balance;
//
//            // Check if user has enough balance (if they need to pay)
//            if ($difference < 0) {
//                $amountToPay = abs($difference);
//                if ($currentBalance < $amountToPay) {
//                    return response()->json([
//                        'status' => false,
//                        'message' => "Insufficient wallet balance. Required: BDT " . number_format($amountToPay, 2) . ", Available: BDT " . number_format($currentBalance, 2)
//                    ], 400);
//                }
//            }
//
//            DB::beginTransaction();
//
//            // =====================================================
//            // WALLET TRANSACTION
//            // =====================================================
//
//            if ($difference > 0) {
//                // User gets REFUND (paid more than needed)
//                $refundAmount = $difference;
//
//                $user->increment('credit_balance', $refundAmount);
//
//                Transaction::create([
//                    'user_id' => $user->id,
//                    'booking_id' => $newBooking->id,
//                    'ref_id' => $reissue->id,
//                    'type' => 'credit',
//                    'transaction_type' => 'reissue_refund',
//                    'amount' => $refundAmount,
//                    'status' => 'refund',
//                    'reference' => 'Reissue Refund - Booking #' . $newBooking->code,
//                    'remarks' => 'Reissue refund: Old booking #' . $oldBooking->id . ' paid BDT ' . number_format($oldBookingPaid, 2) . ', New booking total BDT ' . number_format($totalNeeded, 2) . ', Excess refunded',
//                    'meta' => json_encode([
//                        'balance_before' => $currentBalance,
//                        'balance_after' => $currentBalance + $refundAmount,
//                        'old_booking_id' => $oldBooking->id,
//                        'old_booking_paid' => $oldBookingPaid,
//                        'new_booking_id' => $newBooking->id,
//                        'new_booking_total' => $newBookingTotal,
//                        'new_booking_paid' => $newBookingPaid,
//                        'reissue_charges' => $reissueCharges,
//                        'total_available' => $totalAvailable,
//                        'total_needed' => $totalNeeded,
//                        'refund_amount' => $refundAmount,
//                    ]),
//                    'create_user' => auth()->id(),
//                    'created_at' => now(),
//                    'deposit_date' => now(),
//                ]);
//
//                Log::info('Wallet Credited (Refund)', [
//                    'user_id' => $user->id,
//                    'refund_amount' => $refundAmount,
//                    'balance_before' => $currentBalance,
//                    'balance_after' => $user->fresh()->credit_balance
//                ]);
//
//            } elseif ($difference < 0) {
//                // User needs to PAY (shortage)
//                $amountToPay = abs($difference);
//
//                $user->decrement('credit_balance', $amountToPay);
//
//                Transaction::create([
//                    'user_id' => $user->id,
//                    'booking_id' => $newBooking->id,
//                    'ref_id' => $reissue->id,
//                    'type' => 'debit',
//                    'transaction_type' => 'reissue_payment',
//                    'amount' => $amountToPay,
//                    'status' => 'payment',
//                    'reference' => 'Reissue Payment - Booking #' . $newBooking->code,
//                    'remarks' => 'Reissue payment: Old booking #' . $oldBooking->id . ' paid BDT ' . number_format($oldBookingPaid, 2) . ', New booking total BDT ' . number_format($totalNeeded, 2) . ', Shortage deducted',
//                    'meta' => json_encode([
//                        'balance_before' => $currentBalance,
//                        'balance_after' => $currentBalance - $amountToPay,
//                        'old_booking_id' => $oldBooking->id,
//                        'old_booking_paid' => $oldBookingPaid,
//                        'new_booking_id' => $newBooking->id,
//                        'new_booking_total' => $newBookingTotal,
//                        'new_booking_paid' => $newBookingPaid,
//                        'reissue_charges' => $reissueCharges,
//                        'total_available' => $totalAvailable,
//                        'total_needed' => $totalNeeded,
//                        'amount_paid' => $amountToPay,
//                    ]),
//                    'create_user' => auth()->id(),
//                    'created_at' => now(),
//                    'deposit_date' => now(),
//                ]);
//
//                Log::info('Wallet Debited (Payment)', [
//                    'user_id' => $user->id,
//                    'amount_paid' => $amountToPay,
//                    'balance_before' => $currentBalance,
//                    'balance_after' => $user->fresh()->credit_balance
//                ]);
//
//            } else {
//                // Exact match - no wallet transaction needed
//                Log::info('No wallet transaction needed (exact match)', [
//                    'total_available' => $totalAvailable,
//                    'total_needed' => $totalNeeded
//                ]);
//            }
//
//            // =====================================================
//            // UPDATE RECORDS
//            // =====================================================
//
//            // Update Reissue Record
//            $reissue->update([
//                'status' => 'completed',
//                'fare_difference' => $newBookingTotal - $oldBookingPaid,
//                'total_amount' => abs($difference),
//                'processed_by' => auth()->id(),
//                'processed_at' => now()
//            ]);
//
//            // Update New Booking - mark as fully paid
//            $newBooking->update([
//                'paid' => $newBookingTotal,
//                'pay_now' => 0,
//                'status' => 'booked',
//            ]);
//
//            // Update Old Booking Passengers Status
//            if (!empty($passengerIds)) {
//                BookingPassenger::whereIn('id', $passengerIds)
//                    ->where('booking_id', $oldBooking->id)
//                    ->update(['status' => 'reissued']);
//            }
//
//            // Update Old Booking Status
//            $totalPassengers = BookingPassenger::where('booking_id', $oldBooking->id)->count();
//            $remainingPassengers = $totalPassengers - $passengerCount;
//
//            if ($remainingPassengers > 0) {
//                $oldBooking->update(['status' => 'partial_reissued']);
//            } else {
//                $oldBooking->update(['status' => 'reissued']);
//            }
//
//            DB::commit();
//
//            Log::info('Reissue Approved Successfully', [
//                'reissue_id' => $reissue->id,
//                'old_booking_id' => $oldBooking->id,
//                'new_booking_id' => $newBooking->id,
//                'difference' => $difference
//            ]);
//
//            // =====================================================
//            // RESPONSE MESSAGE
//            // =====================================================
//
//            $message = "Reissue approved successfully!\n\n";
//            $message .= "Old Booking: #{$oldBooking->id} (Paid: BDT " . number_format($oldBookingPaid, 2) . ")\n";
//            $message .= "New Booking: #{$newBooking->id} (Total: BDT " . number_format($newBookingTotal, 2) . ")\n";
//            $message .= "Reissue Charges: BDT " . number_format($reissueCharges, 2) . "\n\n";
//
//            if ($difference > 0) {
//                $message .= "Refunded to wallet: BDT " . number_format($difference, 2);
//            } elseif ($difference < 0) {
//                $message .= "Deducted from wallet: BDT " . number_format(abs($difference), 2);
//            } else {
//                $message .= "No additional payment required (exact match)";
//            }
//
//            return response()->json([
//                'status' => true,
//                'message' => $message,
//                'data' => [
//                    'old_booking_paid' => $oldBookingPaid,
//                    'new_booking_total' => $newBookingTotal,
//                    'reissue_charges' => $reissueCharges,
//                    'total_available' => $totalAvailable,
//                    'total_needed' => $totalNeeded,
//                    'difference' => $difference,
//                    'wallet_action' => $difference > 0 ? 'refund' : ($difference < 0 ? 'deduct' : 'none')
//                ]
//            ]);
//
//        } catch (\Exception $e) {
//            DB::rollBack();
//            Log::error('Reissue Approve Error', [
//                'reissue_id' => $id,
//                'error' => $e->getMessage(),
//                'line' => $e->getLine(),
//                'file' => $e->getFile()
//            ]);
//            return response()->json([
//                'status' => false,
//                'message' => 'Error: ' . $e->getMessage()
//            ], 500);
//        }
//    }

    public function reject(Request $request, $id)
    {
        Log::info('Reissue Reject Request', [
            'reissue_id' => $id,
            'reason' => $request->rejection_reason,
            'user_id' => auth()->id()
        ]);

        try {
            $request->validate([
                'rejection_reason' => 'required|string|min:10'
            ]);

            $reissue = BookingReissue::findOrFail($id);

            if (!in_array($reissue->status, ['pending', 'waiting_user_approval'])) {
                return response()->json([
                    'status' => false,
                    'message' => 'This reissue cannot be rejected at current status.'
                ], 400);
            }

            DB::beginTransaction();

            $oldBooking = $reissue->booking;
            $newBooking = Booking::find($reissue->new_booking_id);

            // Update Reissue Record
            $reissue->update([
                'status' => 'rejected',
                'reason' => $request->rejection_reason,
                'processed_by' => auth()->id(),
                'processed_at' => now()
            ]);

            // Cancel New Booking if exists
            if ($newBooking) {
                $newBooking->update([
                    'status' => 'cancelled',
                ]);
            }

            // Reset Passengers Status
            $passengerIds = is_array($reissue->passenger_ids)
                ? $reissue->passenger_ids
                : json_decode($reissue->passenger_ids, true);

            if (!empty($passengerIds)) {
                BookingPassenger::whereIn('id', $passengerIds)
                    ->where('booking_id', $oldBooking->id)
                    ->update(['status' => 'ticketed']);
            }

            DB::commit();

            Log::info('Reissue Rejected Successfully', [
                'reissue_id' => $reissue->id
            ]);

            return response()->json([
                'status' => true,
                'message' => "Reissue request rejected successfully. Old Booking #{$oldBooking->id} remains active."
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reissue Reject Error', [
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkAction(Request $request)
    {
        $ids = $request->ids;
        $action = $request->action;

        if (empty($ids) || empty($action)) {
            return back()->with('error', 'Please select items and action');
        }

        try {
            switch ($action) {
                case 'delete':
                    $deleted = BookingReissue::whereIn('id', $ids)
                        ->whereIn('status', ['pending', 'rejected'])
                        ->delete();
                    return back()->with('success', "Deleted {$deleted} reissue request(s)");

                default:
                    return back()->with('error', 'Invalid action');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
//
//    public function getPassengers($id)
//    {
//        try {
//            $reissue = BookingReissue::findOrFail($id);
//
//            $savedData  = json_decode($reissue->passenger_fare_details, true);
//            $paxDetails = $savedData['passenger_details'] ?? [];
//
//            if (empty($paxDetails)) {
//                return response()->json([
//                    'status'  => false,
//                    'message' => 'No passenger fare details found.'
//                ], 400);
//            }
//
//            $paxIds     = array_map('intval', array_column($paxDetails, 'passenger_id'));
//            $passengers = BookingPassenger::whereIn('id', $paxIds)->get()->keyBy('id');
//
//            // paxDetails keyed by passenger_id
//            $paxFareMap = [];
//            foreach ($paxDetails as $pd) {
//                $paxFareMap[(int)$pd['passenger_id']] = $pd;
//            }
//
//            $result = [];
//            foreach ($paxIds as $pid) {
//                $p    = $passengers->get($pid);
//                $fare = $paxFareMap[$pid] ?? [];
//
//                if (!$p) continue;
//
//                $oldFare       = (float)($fare['old_fare']       ?? 0);
//                $newFare       = (float)($fare['new_fare']       ?? 0);
//                $reissueCharge = (float)($fare['reissue_charge'] ?? 0);
//                $serviceCharge = (float)($fare['service_charge'] ?? 0);
//                $fareDiff      = $newFare - $oldFare;
//                $extra         = $fareDiff + $reissueCharge + $serviceCharge;
//
//                $result[] = [
//                    'id'             => $p->id,
//                    'first_name'     => $p->first_name,
//                    'last_name'      => $p->last_name,
//                    'traveler_type'  => strtoupper($p->traveler_type ?? 'ADULT'),
//                    'old_fare'       => $oldFare,
//                    'new_fare'       => $newFare,
//                    'fare_diff'      => $fareDiff,
//                    'reissue_charge' => $reissueCharge,
//                    'service_charge' => $serviceCharge,
//                    'extra'          => $extra,
//                ];
//            }
//
//            return response()->json([
//                'status'     => true,
//                'passengers' => $result,
//            ]);
//
//        } catch (\Exception $e) {
//            Log::error('Reissue GetPassengers Error', [
//                'reissue_id' => $id,
//                'error'      => $e->getMessage(),
//            ]);
//            return response()->json([
//                'status'  => false,
//                'message' => 'Error: ' . $e->getMessage()
//            ], 500);
//        }
//    }
}
