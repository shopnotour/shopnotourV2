<?php
////
////
////namespace Modules\Booking\Controllers;
////
////
////use Illuminate\Http\Request;
////use Illuminate\Support\Facades\Log;
////use Modules\Booking\Models\Booking;
////use Modules\Booking\Models\BookingPassenger;
////
////class NormalCheckoutController extends BookingController
////{
////    public function showInfo(){
////        return view("Booking::frontend.normal-checkout.info");
////    }
////    public function confirmPayment(Request $request, $gateway)
////    {
////        $gateways = get_payment_gateways();
////        if (empty($gateways[$gateway]) or !class_exists($gateways[$gateway])) {
////            return $this->sendError(__("Payment gateway not found"));
////        }
////        $gatewayObj = new $gateways[$gateway]($gateway);
////        if (!$gatewayObj->isAvailable()) {
////            return $this->sendError(__("Payment gateway is not available"));
////        }
////        $res = $gatewayObj->confirmNormalPayment($request);
////        $status = $res[0] ?? null;
////        $message = $res[1] ?? null;
////        $redirect_url = $res[2] ?? null;
////
////        if(empty($redirect_url)) $redirect_url = route('gateway.info');
////
////        return redirect()->to($redirect_url)->with($status ? "success" : "error",$message);
////
////    }
////
////    public function sendError($message, $data = [])
////    {
////        return  redirect()->to(route('gateway.info'))->with('error',$message);
////    }
////
////    public function cancelPayment(Request $request, $gateway)
////    {
////
////        $gateways = get_payment_gateways();
////        if (empty($gateways[$gateway]) or !class_exists($gateways[$gateway])) {
////            return $this->sendError(__("Payment gateway not found"));
////        }
////        $gatewayObj = new $gateways[$gateway]($gateway);
////        if (!$gatewayObj->isAvailable()) {
////            return $this->sendError(__("Payment gateway is not available"));
////        }
////        $res =  $gatewayObj->cancelNormalPayment($request);
////        $status = $res[0] ?? null;
////        $message = $res[1] ?? null;
////        $redirect_url = $res[2] ?? null;
////
////        if(empty($redirect_url)) $redirect_url = route('gateway.info');
////
////        return redirect()->to($redirect_url)->with($status ? "success" : "error",$message);
////    }
////
//////    =======================================================
////    public function handleBookingAction(Request $request)
////    {
////
////        try {
////            $action = $request->input('action');
////            $bookingId = $request->input('booking_id');
////
////            // Validate booking exists
////            $booking = Booking::find($bookingId);
////            if (!$booking) {
////                return response()->json([
////                    'success' => false,
////                    'message' => 'Booking not found'
////                ], 404);
////            }
////
////            // Route to appropriate handler based on action
////            switch ($action) {
////                case 'issue':
////                    return $this->handleIssueTicket($request, $booking);
////
////                case 'void':
////                    return $this->handleVoidTicket($request, $booking);
////
////                case 'refund':
////                    return $this->handleRefund($request, $booking);
////
////                case 'reissue':
////                    return $this->handleReissue($request, $booking);
////
////                case 'passport':
////                    return $this->handlePassportUpdate($request, $booking);
////
////                case 'addssr':
////                    return $this->handleAddSSR($request, $booking);
////
////                case 'confirm':
////                    return $this->handleConfirmBooking($request, $booking);
////
////                case 'cancel':
////                    return $this->handleCancelBooking($request, $booking);
////
////                default:
////                    return response()->json([
////                        'success' => false,
////                        'message' => 'Invalid action'
////                    ], 400);
////            }
////
////        } catch (\Exception $e) {
////            Log::error('Booking action error: ' . $e->getMessage());
////
////            return response()->json([
////                'success' => false,
////                'message' => 'An error occurred: ' . $e->getMessage()
////            ], 500);
////        }
////    }
////
////    /**
////     * Handle Issue Ticket
////     */
////    private function handleIssueTicket(Request $request, $booking)
////    {
////        $request->validate([
////            'passenger_id' => 'required|exists:flight_passengers,id',
////            'pnr_number' => 'required|string',
////        ]);
////
////        $passenger = BookingPassenger::find($request->passenger_id);
////
////        // Update passenger with ticket info
//////        $passenger->update([
//////            'ticket_number' => $this->generateTicketNumber(),
//////            'booking_status' => 'issued'
//////        ]);
////
////        // Update booking PNR if not set
////        if (!$booking->pnr_id) {
////            $booking->pnr_id = $request->pnr_number;
////            $booking->save();
////        }
////
////        // Log activity
////        $this->logBookingActivity($booking->id, 'issue', [
////            'passenger_id' => $passenger->id,
////            'passenger_name' => $passenger->first_name . ' ' . $passenger->last_name,
////            'ticket_number' => $passenger->ticket_number,
////            'notes' => $request->notes
////        ]);
////
////        return response()->json([
////            'success' => true,
////            'message' => 'Ticket issued successfully for ' . $passenger->first_name . ' ' . $passenger->last_name,
////            'data' => [
////                'ticket_number' => $passenger->ticket_number
////            ]
////        ]);
////    }
////
////    /**
////     * Handle Void Ticket
////     */
////    private function handleVoidTicket(Request $request, $booking)
////    {
////        return $request;
////        // Check 24 hour limit
////        $bookingTime = Carbon::parse($booking->created_at);
////        $currentTime = Carbon::now();
////        $hoursDiff = $bookingTime->diffInHours($currentTime);
////
////        if ($hoursDiff > 24) {
////            return response()->json([
////                'success' => false,
////                'message' => 'Void period has expired. Tickets can only be voided within 24 hours of booking.'
////            ], 400);
////        }
////
////        $request->validate([
////            'pnr_number' => 'required|string',
////            'reason' => 'required|string|min:10'
////        ]);
////
////        // Get service charge from flight_charge table
////        $serviceCharge = DB::table('flight_charge')
////            ->where('booking_id', $booking->id)
////            ->value('void_charge') ?? 0;
////
////        // Update booking status
////        $booking->status = 'voided';
////        $booking->void_reason = $request->reason;
////        $booking->void_charge = $serviceCharge;
////        $booking->voided_at = Carbon::now();
////        $booking->save();
////
////        // Update all passengers status
////        FlightPassenger::where('booking_id', $booking->id)
////            ->update(['booking_status' => 'voided']);
////
////        // Log activity
////        $this->logBookingActivity($booking->id, 'void', [
////            'reason' => $request->reason,
////            'service_charge' => $serviceCharge,
////            'voided_at' => Carbon::now()
////        ]);
////
////        return response()->json([
////            'success' => true,
////            'message' => 'Ticket voided successfully. Service charge: ৳' . number_format($serviceCharge, 2)
////        ]);
////    }
////
////    /**
////     * Handle Refund
////     */
////    private function handleRefund(Request $request, $booking)
////    {
////        // Check payment status
////        if ($booking->payment_status !== 'paid') {
////            return response()->json([
////                'success' => false,
////                'message' => 'Refund is only available for paid bookings.'
////            ], 400);
////        }
////
////        $request->validate([
////            'passenger_id' => 'nullable|exists:flight_passengers,id',
////            'refund_amount' => 'nullable|numeric|min:0',
////            'refund_type' => 'nullable|in:full,partial,cancellation',
////            'refund_reason' => 'required|string|min:10'
////        ]);
////
////        $refundCharge = 500; // Default charge
////        $refundAmount = $request->refund_amount ?? $booking->total;
////        $netRefund = $refundAmount - $refundCharge;
////
////        DB::beginTransaction();
////        try {
////            // Create refund record
////            $refundId = DB::table('booking_refunds')->insertGetId([
////                'booking_id' => $booking->id,
////                'passenger_id' => $request->passenger_id,
////                'refund_type' => $request->refund_type ?? 'full',
////                'refund_amount' => $refundAmount,
////                'refund_charge' => $refundCharge,
////                'net_refund' => $netRefund,
////                'refund_reason' => $request->refund_reason,
////                'pnr_number' => $request->pnr_number,
////                'status' => 'pending',
////                'requested_at' => Carbon::now(),
////                'created_at' => Carbon::now(),
////                'updated_at' => Carbon::now()
////            ]);
////
////            // Update booking status
////            $booking->refund_status = 'processing';
////            $booking->save();
////
////            // Update passenger status if specific passenger
////            if ($request->passenger_id) {
////                FlightPassenger::where('id', $request->passenger_id)
////                    ->update(['booking_status' => 'refund_requested']);
////            }
////
////            // Log activity
////            $this->logBookingActivity($booking->id, 'refund', [
////                'refund_id' => $refundId,
////                'passenger_id' => $request->passenger_id,
////                'refund_amount' => $refundAmount,
////                'refund_charge' => $refundCharge,
////                'net_refund' => $netRefund,
////                'reason' => $request->refund_reason
////            ]);
////
////            DB::commit();
////
////            return response()->json([
////                'success' => true,
////                'message' => 'Refund request submitted successfully.',
////                'data' => [
////                    'refund_id' => $refundId,
////                    'refund_amount' => $refundAmount,
////                    'refund_charge' => $refundCharge,
////                    'net_refund' => $netRefund
////                ]
////            ]);
////
////        } catch (\Exception $e) {
////            DB::rollBack();
////            throw $e;
////        }
////    }
////
////    /**
////     * Handle Reissue
////     */
////    private function handleReissue(Request $request, $booking)
////    {
////        return $request;
//////        $request->validate([
//////            'passenger_id' => 'nullable|exists:flight_passengers,id',
//////            'new_flight_date' => 'required|date|after:today',
//////            'remarks' => 'nullable|string'
//////        ]);
////
////        DB::beginTransaction();
////        try {
////            // Create reissue record
////            $reissueId = DB::table('booking_reissues')->insertGetId([
////                'booking_id' => $booking->id,
////                'passenger_id' => $request->passenger_id,
////                'new_flight_date' => $request->new_flight_date,
////                'pnr_number' => $request->pnr_number,
////                'remarks' => $request->remarks,
////                'status' => 'pending',
////                'requested_at' => Carbon::now(),
////                'created_at' => Carbon::now(),
////                'updated_at' => Carbon::now()
////            ]);
////
////            // Update booking status
////            $booking->reissue_status = 'processing';
////            $booking->save();
////
////            // Update passenger status if specific passenger
////            if ($request->passenger_id) {
////                FlightPassenger::where('id', $request->passenger_id)
////                    ->update(['booking_status' => 'reissue_requested']);
////            }
////
////            // Log activity
////            $this->logBookingActivity($booking->id, 'reissue', [
////                'reissue_id' => $reissueId,
////                'passenger_id' => $request->passenger_id,
////                'new_flight_date' => $request->new_flight_date,
////                'remarks' => $request->remarks
////            ]);
////
////            DB::commit();
////
////            return response()->json([
////                'success' => true,
////                'message' => 'Reissue request submitted successfully.',
////                'data' => [
////                    'reissue_id' => $reissueId,
////                    'new_flight_date' => $request->new_flight_date
////                ]
////            ]);
////
////        } catch (\Exception $e) {
////            DB::rollBack();
////            throw $e;
////        }
////    }
////
////    /**
////     * Handle Passport Update
////     */
////    private function handlePassportUpdate(Request $request, $booking)
////    {
////        $request->validate([
////            'passenger_id' => 'required|exists:flight_passengers,id',
////            'passport_number' => 'required|string',
////            'expiry_date' => 'required|date|after:today',
////            'issuing_country' => 'nullable|string'
////        ]);
////
////        $passenger = FlightPassenger::find($request->passenger_id);
////
////        $passenger->update([
////            'passport_number' => $request->passport_number,
////            'passport_expiry_date' => $request->expiry_date,
////            'passport_country' => $request->issuing_country
////        ]);
////
////        // Log activity
////        $this->logBookingActivity($booking->id, 'passport_update', [
////            'passenger_id' => $passenger->id,
////            'passenger_name' => $passenger->first_name . ' ' . $passenger->last_name,
////            'passport_number' => $request->passport_number
////        ]);
////
////        return response()->json([
////            'success' => true,
////            'message' => 'Passport details updated successfully for ' . $passenger->first_name . ' ' . $passenger->last_name
////        ]);
////    }
////
////    /**
////     * Handle Add SSR
////     */
////    private function handleAddSSR(Request $request, $booking)
////    {
////        $request->validate([
////            'passenger_id' => 'required|exists:flight_passengers,id',
////            'ssr_type' => 'required|string',
////            'ssr_details' => 'required|string'
////        ]);
////
////        DB::beginTransaction();
////        try {
////            // Create SSR record
////            $ssrId = DB::table('booking_ssrs')->insertGetId([
////                'booking_id' => $booking->id,
////                'passenger_id' => $request->passenger_id,
////                'pnr_number' => $request->pnr_number,
////                'ssr_type' => $request->ssr_type,
////                'ssr_details' => $request->ssr_details,
////                'status' => 'pending',
////                'created_at' => Carbon::now(),
////                'updated_at' => Carbon::now()
////            ]);
////
////            $passenger = FlightPassenger::find($request->passenger_id);
////
////            // Log activity
////            $this->logBookingActivity($booking->id, 'add_ssr', [
////                'ssr_id' => $ssrId,
////                'passenger_id' => $passenger->id,
////                'passenger_name' => $passenger->first_name . ' ' . $passenger->last_name,
////                'ssr_type' => $request->ssr_type,
////                'ssr_details' => $request->ssr_details
////            ]);
////
////            DB::commit();
////
////            return response()->json([
////                'success' => true,
////                'message' => 'SSR added successfully for ' . $passenger->first_name . ' ' . $passenger->last_name,
////                'data' => [
////                    'ssr_id' => $ssrId
////                ]
////            ]);
////
////        } catch (\Exception $e) {
////            DB::rollBack();
////            throw $e;
////        }
////    }
////
////    /**
////     * Handle Confirm Booking
////     */
////    private function handleConfirmBooking(Request $request, $booking)
////    {
////        if ($booking->status !== 'processing') {
////            return response()->json([
////                'success' => false,
////                'message' => 'Only processing bookings can be confirmed.'
////            ], 400);
////        }
////
////        $booking->status = 'confirmed';
////        $booking->confirmation_number = $request->confirmation_number ?? $this->generateConfirmationNumber();
////        $booking->confirmation_notes = $request->confirmation_notes;
////        $booking->confirmed_at = Carbon::now();
////        $booking->save();
////
////        // Update all passengers status
////        FlightPassenger::where('booking_id', $booking->id)
////            ->update(['booking_status' => 'confirmed']);
////
////        // Log activity
////        $this->logBookingActivity($booking->id, 'confirm', [
////            'confirmation_number' => $booking->confirmation_number,
////            'notes' => $request->confirmation_notes
////        ]);
////
////        return response()->json([
////            'success' => true,
////            'message' => 'Booking confirmed successfully.'
////        ]);
////    }
////
////    /**
////     * Handle Cancel Booking
////     */
////    private function handleCancelBooking(Request $request, $booking)
////    {
////        if ($booking->status === 'cancelled') {
////            return response()->json([
////                'success' => false,
////                'message' => 'Booking is already cancelled.'
////            ], 400);
////        }
////
////        $request->validate([
////            'cancellation_type' => 'required|in:customer_request,no_show,flight_cancelled,other',
////            'cancellation_reason' => 'required|string|min:10'
////        ]);
////
////        $booking->status = 'cancelled';
////        $booking->cancellation_type = $request->cancellation_type;
////        $booking->cancellation_reason = $request->cancellation_reason;
////        $booking->cancelled_at = Carbon::now();
////        $booking->save();
////
////        // Update all passengers status
////        FlightPassenger::where('booking_id', $booking->id)
////            ->update(['booking_status' => 'cancelled']);
////
////        // Log activity
////        $this->logBookingActivity($booking->id, 'cancel', [
////            'cancellation_type' => $request->cancellation_type,
////            'reason' => $request->cancellation_reason
////        ]);
////
////        return response()->json([
////            'success' => true,
////            'message' => 'Booking cancelled successfully.'
////        ]);
////    }
////
////    /**
////     * Helper: Generate ticket number
////     */
////    private function generateTicketNumber()
////    {
////        return 'TKT-' . strtoupper(uniqid());
////    }
////
////    /**
////     * Helper: Generate confirmation number
////     */
////    private function generateConfirmationNumber()
////    {
////        return 'CONF-' . strtoupper(substr(md5(uniqid()), 0, 8));
////    }
////
////    /**
////     * Helper: Log booking activity
////     */
////    private function logBookingActivity($bookingId, $action, $data = [])
////    {
////        DB::table('booking_activity_logs')->insert([
////            'booking_id' => $bookingId,
////            'user_id' => auth()->id(),
////            'action' => $action,
////            'data' => json_encode($data),
////            'ip_address' => request()->ip(),
////            'user_agent' => request()->userAgent(),
////            'created_at' => Carbon::now(),
////            'updated_at' => Carbon::now()
////        ]);
////    }
////
////}
//
//
//namespace Modules\Booking\Controllers;
//
//use Carbon\Carbon;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Log;
//use Modules\Booking\Models\Booking;
//use Modules\Booking\Models\BookingPassenger;
//use Modules\Booking\Models\BookingRefund;
//use Modules\Booking\Models\BookingReissue;
//use Modules\Booking\Models\BookingVoid;
//use Modules\Booking\Models\BookingSSR;
//
//class NormalCheckoutController extends BookingController
//{
//    public function showInfo()
//    {
//        return view("Booking::frontend.normal-checkout.info");
//    }
//
//    public function confirmPayment(Request $request, $gateway)
//    {
//        $gateways = get_payment_gateways();
//        if (empty($gateways[$gateway]) or !class_exists($gateways[$gateway])) {
//            return $this->sendError(__("Payment gateway not found"));
//        }
//        $gatewayObj = new $gateways[$gateway]($gateway);
//        if (!$gatewayObj->isAvailable()) {
//            return $this->sendError(__("Payment gateway is not available"));
//        }
//        $res = $gatewayObj->confirmNormalPayment($request);
//        $status = $res[0] ?? null;
//        $message = $res[1] ?? null;
//        $redirect_url = $res[2] ?? null;
//
//        if (empty($redirect_url)) $redirect_url = route('gateway.info');
//
//        return redirect()->to($redirect_url)->with($status ? "success" : "error", $message);
//    }
//
//    public function sendError($message, $data = [])
//    {
//        return redirect()->to(route('gateway.info'))->with('error', $message);
//    }
//
//    public function cancelPayment(Request $request, $gateway)
//    {
//        $gateways = get_payment_gateways();
//        if (empty($gateways[$gateway]) or !class_exists($gateways[$gateway])) {
//            return $this->sendError(__("Payment gateway not found"));
//        }
//        $gatewayObj = new $gateways[$gateway]($gateway);
//        if (!$gatewayObj->isAvailable()) {
//            return $this->sendError(__("Payment gateway is not available"));
//        }
//        $res = $gatewayObj->cancelNormalPayment($request);
//        $status = $res[0] ?? null;
//        $message = $res[1] ?? null;
//        $redirect_url = $res[2] ?? null;
//
//        if (empty($redirect_url)) $redirect_url = route('gateway.info');
//
//        return redirect()->to($redirect_url)->with($status ? "success" : "error", $message);
//    }
//
//    //=======================================================
//    // BOOKING ACTION HANDLER
//    //=======================================================
//
//    public function handleBookingAction(Request $request)
//    {
//        Log::info('📥 Booking Action Request', [
//            'data' => $request->all(),
//            'action' => $request->action,
//            'user_id' => auth()->id()
//        ]);
//
//        try {
//            $action = $request->input('action');
//            $bookingId = $request->input('booking_id');
//
//            // Validate booking exists
//            $booking = Booking::find($bookingId);
//            if (!$booking) {
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Booking not found'
//                ], 404);
//            }
//
//            // Route to appropriate handler
//            switch ($action) {
//                case 'issue':
//                    return $this->handleIssueTicket($request, $booking);
//
//                case 'void':
//                    return $this->handleVoidTicket($request, $booking);
//
//                case 'refund':
//                    return $this->handleRefund($request, $booking);
//
//                case 'reissue':
//                    return $this->handleReissue($request, $booking);
//
//                case 'passport':
//                    return $this->handlePassportUpdate($request, $booking);
//
//                case 'addssr':
//                    return $this->handleAddSSR($request, $booking);
//
//                case 'confirm':
//                    return $this->handleConfirmBooking($request, $booking);
//
//                case 'cancel':
//                    return $this->handleCancelBooking($request, $booking);
//
//                default:
//                    return response()->json([
//                        'success' => false,
//                        'message' => 'Invalid action: ' . $action
//                    ], 400);
//            }
//
//        } catch (\Exception $e) {
//            Log::error('❌ Booking Action Error', [
//                'error' => $e->getMessage(),
//                'trace' => $e->getTraceAsString()
//            ]);
//
//            return response()->json([
//                'success' => false,
//                'message' => 'An error occurred: ' . $e->getMessage()
//            ], 500);
//        }
//    }
//
//    /**
//     * ✅ Handle Issue Ticket
//     */
//    private function handleIssueTicket(Request $request, $booking)
//    {
//        try {
//            $request->validate([
//                'passenger_id' => 'required|exists:flight_passengers,id',
//                'pnr_number' => 'required|string',
//            ]);
//
//            $passenger = BookingPassenger::find($request->passenger_id);
//
//            if ($passenger->ticket_number) {
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Ticket already issued for this passenger'
//                ], 400);
//            }
//
//            DB::beginTransaction();
//
//            // Generate ticket number
//            $ticketNumber = $this->generateTicketNumber();
//
//            // Update passenger
//            $passenger->update([
//                'ticket_number' => $ticketNumber,
//                'booking_status' => 'issued',
//                'ticket_issued_at' => Carbon::now()
//            ]);
//
//            // Update booking PNR if not set
//            if (!$booking->pnr_id) {
//                $booking->pnr_id = $request->pnr_number;
//                $booking->save();
//            }
//
//            // Log activity
//            $this->logBookingActivity($booking->id, 'issue', [
//                'passenger_id' => $passenger->id,
//                'passenger_name' => $passenger->first_name . ' ' . $passenger->last_name,
//                'ticket_number' => $ticketNumber,
//                'notes' => $request->notes
//            ]);
//
//            DB::commit();
//
//            Log::info('✅ Ticket Issued', [
//                'booking_id' => $booking->id,
//                'passenger_id' => $passenger->id,
//                'ticket_number' => $ticketNumber
//            ]);
//
//            return response()->json([
//                'success' => true,
//                'message' => 'Ticket issued successfully for ' . $passenger->first_name . ' ' . $passenger->last_name,
//                'data' => [
//                    'ticket_number' => $ticketNumber,
//                    'passenger_name' => $passenger->first_name . ' ' . $passenger->last_name
//                ]
//            ]);
//
//        } catch (\Exception $e) {
//            DB::rollBack();
//            Log::error('Issue Ticket Error', ['error' => $e->getMessage()]);
//            throw $e;
//        }
//    }
//
//    /**
//     * ✅ Handle Void Ticket
//     */
//    private function handleVoidTicket(Request $request, $booking)
//    {
//        try {
//            // Check 24 hour limit
//            $bookingTime = Carbon::parse($booking->confirmed_at);
//            $currentTime = Carbon::now();
//            $hoursDiff = $bookingTime->diffInHours($currentTime);
//
//            if ($hoursDiff > 24) {
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Void period expired. Tickets can only be voided within 24 hours of booking.'
//                ], 400);
//            }
//
//            $request->validate([
//                'pnr_number' => 'required|string',
//                'reason' => 'required|string|min:10'
//            ]);
//
//            DB::beginTransaction();
//
//            // Get void charge (default 500 BDT)
//            $voidCharge = 500;
//
//            // Create void record
//            $void = BookingVoid::create([
//                'booking_id' => $booking->id,
//                'pnr' => $request->pnr_number??'',
//                'void_charges' => $voidCharge,
//                'status' => 'pending',
//                'reason' => $request->reason,
//                'voided_by' => auth()->id(),
//                'voided_at' => Carbon::now()
//            ]);
//
//            // Update booking status
////            $booking->update([
////                'status' => 'voided',
////                'void_reason' => $request->reason,
////                'void_charge' => $voidCharge,
////                'voided_at' => Carbon::now()
////            ]);
//
//            // Update all passengers status
////            BookingPassenger::where('booking_id', $booking->id)
////                ->update(['booking_status' => 'voided']);
//
//            // Log activity
////            $this->logBookingActivity($booking->id, 'void', [
////                'void_id' => $void->id,
////                'reason' => $request->reason,
////                'void_charge' => $voidCharge,
////                'voided_at' => Carbon::now()
////            ]);
//
//            DB::commit();
//
//            Log::info('✅ Ticket Voided', [
//                'booking_id' => $booking->id,
//                'void_id' => $void->id,
//                'void_charge' => $voidCharge
//            ]);
//
//            return response()->json([
//                'success' => true,
//                'message' => 'Ticket voided successfully. Void charge: ৳' . number_format($voidCharge, 2),
//                'data' => [
//                    'void_id' => $void->id,
//                    'void_charge' => $voidCharge
//                ]
//            ]);
//
//        } catch (\Exception $e) {
//            DB::rollBack();
//            Log::error('Void Ticket Error', ['error' => $e->getMessage()]);
//            throw $e;
//        }
//    }
//
//    /**
//     * ✅ Handle Refund
//     */
//    private function handleRefund(Request $request, $booking)
//    {
//        try {
//            DB::beginTransaction();
//
//            $refundCharge = 500; // Default charge
//
//            // ✅ Check if all passengers or specific one
//            $isAllPassengers = empty($request->passenger_id) || $request->passenger_id === 'all';
//
//            // ✅ Get passengers based on selection
//            if ($isAllPassengers) {
//                // All passengers for this booking
//                $passengers = BookingPassenger::where('booking_id', $booking->id)->get();
//                $passengerIds = $passengers->pluck('id')->toArray();
//                $refundAmount = $request->refund_amount ?? $booking->total;
//            } else {
//                // Specific passenger
//                $passengers = BookingPassenger::where('id', $request->passenger_id)->get();
//                $passengerIds = [$request->passenger_id];
//
//                // Calculate refund for single passenger
//                $singlePassenger = $passengers->first();
//                $refundAmount = $request->refund_amount ?? ($singlePassenger->total ?? $booking->total / $booking->total_guests);
//            }
//
//            $netRefund = $refundAmount - $refundCharge;
//
//            // Create refund record
//            $refund = BookingRefund::create([
//                'booking_id' => $booking->id,
//                'passenger_id' => $isAllPassengers ? null : $request->passenger_id, // null = all
//                'pnr' => $request->pnr_number,
//                'refund_type' => $request->refund_type ?? ($isAllPassengers ? 'full' : 'partial'),
//                'refund_amount' => $refundAmount,
//                'refund_charges' => $refundCharge,
//                'net_refund_amount' => $netRefund,
//                'status' => 'pending',
//                'reason' => $request->refund_reason,
//                'requested_by' => auth()->id(),
//                'requested_at' => Carbon::now()
//            ]);
//
//            // ✅ Update ALL affected passengers
////            BookingPassenger::whereIn('id', $passengerIds)
////                ->update(['booking_status' => 'refund_requested']);
////
////            // ✅ Update booking status
////            $booking->update([
////                'refund_status' => $isAllPassengers ? 'processing' : 'partial_processing',
////                'status' => $isAllPassengers ? 'refund_requested' : $booking->status
////            ]);
//
//            DB::commit();
//
//            return response()->json([
//                'success' => true,
//                'message' => $isAllPassengers
//                    ? 'Refund requested for all ' . count($passengerIds) . ' passengers.'
//                    : 'Refund requested for selected passenger.',
//                'data' => [
//                    'refund_id' => $refund->id,
//                    'refund_amount' => $refundAmount,
//                    'refund_charge' => $refundCharge,
//                    'net_refund' => $netRefund,
//                    'affected_passengers' => count($passengerIds)
//                ]
//            ]);
//
//        } catch (\Exception $e) {
//            DB::rollBack();
//            Log::error('Refund Error', ['error' => $e->getMessage()]);
//            throw $e;
//        }
//    }
//
//    /**
//     * ✅ Handle Reissue
//     */
//    private function handleReissue(Request $request, $booking)
//    {
//        try {
//            $request->validate([
//                'passenger_id' => 'nullable|exists:flight_passengers,id',
//                'new_flight_date' => 'required|date|after:today',
//                'return_date' => 'nullable|date|after:new_flight_date',
//                'remarks' => 'nullable|string'
//            ]);
//
//            DB::beginTransaction();
//
//            $reissueCharge = 1000; // Default reissue charge
//            $fareDifference = 0; // Calculate based on new fare
//            $totalAmount = $reissueCharge + $fareDifference;
//
//            // Prepare old flight details
//            $oldFlightDetails = [
//                'trip_type' => $booking->flight_type,
//                'from' => $booking->flight_from,
//                'to' => $booking->flight_to,
//                'departure_date' => $booking->start_date,
//                'return_date' => $booking->end_date
//            ];
//
//            // Prepare new flight details
//            $newFlightDetails = [
//                'trip_type' => $request->trip_type ?? $booking->flight_type,
//                'from' => $request->from ?? $booking->flight_from,
//                'to' => $request->to ?? $booking->flight_to,
//                'departure_date' => $request->new_flight_date,
//                'return_date' => $request->return_date
//            ];
//
//            // Create reissue record
//            $reissue = BookingReissue::create([
//                'booking_id' => $booking->id,
//                'passenger_id' => $request->passenger_id,
//                'old_pnr' => $request->pnr_number,
//                'new_pnr' => null, // Will be updated after airline confirmation
//                'reissue_type' => 'date',
//                'reissue_charges' => $reissueCharge,
//                'fare_difference' => $fareDifference,
//                'total_amount' => $totalAmount,
//                'status' => 'pending',
//                'old_flight_details' => $oldFlightDetails,
//                'new_flight_details' => $newFlightDetails,
//                'reason' => $request->remarks,
//                'requested_by' => auth()->id(),
//                'requested_at' => Carbon::now()
//            ]);
//
//            // Update booking reissue status
//            $booking->update([
//                'reissue_status' => 'processing'
//            ]);
//
//            // Update passenger status if specific passenger
//            if ($request->passenger_id) {
//                BookingPassenger::where('id', $request->passenger_id)
//                    ->update(['booking_status' => 'reissue_requested']);
//            }
//
//            // Log activity
//            $this->logBookingActivity($booking->id, 'reissue', [
//                'reissue_id' => $reissue->id,
//                'passenger_id' => $request->passenger_id,
//                'new_flight_date' => $request->new_flight_date,
//                'return_date' => $request->return_date,
//                'reissue_charge' => $reissueCharge,
//                'remarks' => $request->remarks
//            ]);
//
//            DB::commit();
//
//            Log::info('✅ Reissue Requested', [
//                'booking_id' => $booking->id,
//                'reissue_id' => $reissue->id,
//                'new_date' => $request->new_flight_date
//            ]);
//
//            return response()->json([
//                'success' => true,
//                'message' => 'Reissue request submitted successfully.',
//                'data' => [
//                    'reissue_id' => $reissue->id,
//                    'new_flight_date' => $request->new_flight_date,
//                    'reissue_charge' => $reissueCharge,
//                    'total_amount' => $totalAmount
//                ]
//            ]);
//
//        } catch (\Exception $e) {
//            DB::rollBack();
//            Log::error('Reissue Error', ['error' => $e->getMessage()]);
//            throw $e;
//        }
//    }
//
//    /**
//     * ✅ Handle Passport Update
//     */
//    private function handlePassportUpdate(Request $request, $booking)
//    {
//        try {
//            $request->validate([
//                'passenger_id' => 'required|exists:flight_passengers,id',
//                'passport_number' => 'required|string',
//                'expiry_date' => 'required|date|after:today',
//                'issuing_country' => 'nullable|string'
//            ]);
//
//            $passenger = BookingPassenger::find($request->passenger_id);
//
//            $passenger->update([
//                'passport_number' => $request->passport_number,
//                'passport_expiry_date' => $request->expiry_date,
//                'country' => $request->issuing_country ?? $passenger->country
//            ]);
//
//            // Log activity
//            $this->logBookingActivity($booking->id, 'passport_update', [
//                'passenger_id' => $passenger->id,
//                'passenger_name' => $passenger->first_name . ' ' . $passenger->last_name,
//                'passport_number' => $request->passport_number,
//                'expiry_date' => $request->expiry_date
//            ]);
//
//            Log::info('✅ Passport Updated', [
//                'booking_id' => $booking->id,
//                'passenger_id' => $passenger->id
//            ]);
//
//            return response()->json([
//                'success' => true,
//                'message' => 'Passport details updated successfully for ' . $passenger->first_name . ' ' . $passenger->last_name
//            ]);
//
//        } catch (\Exception $e) {
//            Log::error('Passport Update Error', ['error' => $e->getMessage()]);
//            throw $e;
//        }
//    }
//
//    /**
//     * ✅ Handle Add SSR
//     */
//    private function handleAddSSR(Request $request, $booking)
//    {
//        try {
//            $request->validate([
//                'passenger_id' => 'required|exists:flight_passengers,id',
//                'ssr_type' => 'required|in:meal,wheelchair,baggage,seat,infant,medical,other',
//                'ssr_details' => 'required|string'
//            ]);
//
//            DB::beginTransaction();
//
//            $passenger = BookingPassenger::find($request->passenger_id);
//
//            // Determine SSR amount based on type
//            $ssrAmounts = [
//                'meal' => 500,
//                'wheelchair' => 0,
//                'baggage' => 1500,
//                'seat' => 800,
//                'infant' => 0,
//                'medical' => 0,
//                'other' => 0
//            ];
//
//            $ssrAmount = $ssrAmounts[$request->ssr_type] ?? 0;
//
//            // Create SSR record
//            $ssr = BookingSSR::create([
//                'booking_id' => $booking->id,
//                'passenger_id' => $request->passenger_id,
//                'ssr_type' => $request->ssr_type,
//                'description' => $request->ssr_details,
//                'amount' => $ssrAmount,
//                'status' => 'pending',
//                'ssr_details' => [
//                    'type' => $request->ssr_type,
//                    'details' => $request->ssr_details,
//                    'pnr' => $request->pnr_number
//                ],
//                'added_by' => auth()->id()
//            ]);
//
//            // Log activity
//            $this->logBookingActivity($booking->id, 'add_ssr', [
//                'ssr_id' => $ssr->id,
//                'passenger_id' => $passenger->id,
//                'passenger_name' => $passenger->first_name . ' ' . $passenger->last_name,
//                'ssr_type' => $request->ssr_type,
//                'ssr_details' => $request->ssr_details,
//                'amount' => $ssrAmount
//            ]);
//
//            DB::commit();
//
//            Log::info('✅ SSR Added', [
//                'booking_id' => $booking->id,
//                'ssr_id' => $ssr->id,
//                'type' => $request->ssr_type
//            ]);
//
//            return response()->json([
//                'success' => true,
//                'message' => 'SSR added successfully for ' . $passenger->first_name . ' ' . $passenger->last_name,
//                'data' => [
//                    'ssr_id' => $ssr->id,
//                    'ssr_type' => $request->ssr_type,
//                    'amount' => $ssrAmount
//                ]
//            ]);
//
//        } catch (\Exception $e) {
//            DB::rollBack();
//            Log::error('Add SSR Error', ['error' => $e->getMessage()]);
//            throw $e;
//        }
//    }
//
//    /**
//     * ✅ Handle Confirm Booking
//     */
//    private function handleConfirmBooking(Request $request, $booking)
//    {
//        try {
//            if ($booking->status !== 'processing') {
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Only processing bookings can be confirmed.'
//                ], 400);
//            }
//
//            $booking->update([
//                'status' => 'confirmed',
//                'confirmation_number' => $request->confirmation_number ?? $this->generateConfirmationNumber(),
//                'confirmation_notes' => $request->confirmation_notes,
//                'confirmed_at' => Carbon::now()
//            ]);
//
//            // Update all passengers status
//            BookingPassenger::where('booking_id', $booking->id)
//                ->update(['booking_status' => 'confirmed']);
//
//            // Log activity
//            $this->logBookingActivity($booking->id, 'confirm', [
//                'confirmation_number' => $booking->confirmation_number,
//                'notes' => $request->confirmation_notes
//            ]);
//
//            Log::info('✅ Booking Confirmed', [
//                'booking_id' => $booking->id,
//                'confirmation_number' => $booking->confirmation_number
//            ]);
//
//            return response()->json([
//                'success' => true,
//                'message' => 'Booking confirmed successfully.',
//                'data' => [
//                    'confirmation_number' => $booking->confirmation_number
//                ]
//            ]);
//
//        } catch (\Exception $e) {
//            Log::error('Confirm Booking Error', ['error' => $e->getMessage()]);
//            throw $e;
//        }
//    }
//
//    /**
//     * ✅ Handle Cancel Booking
//     */
//    private function handleCancelBooking(Request $request, $booking)
//    {
//        try {
//            if ($booking->status === 'cancelled') {
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Booking is already cancelled.'
//                ], 400);
//            }
//
//            $request->validate([
//                'cancellation_type' => 'required|in:customer_request,no_show,flight_cancelled,other',
//                'cancellation_reason' => 'required|string|min:10'
//            ]);
//
//            DB::beginTransaction();
//
//            $booking->update([
//                'status' => 'cancelled',
//                'cancellation_type' => $request->cancellation_type,
//                'cancellation_reason' => $request->cancellation_reason,
//                'cancelled_at' => Carbon::now()
//            ]);
//
//            // Update all passengers status
//            BookingPassenger::where('booking_id', $booking->id)
//                ->update(['booking_status' => 'cancelled']);
//
//            // Log activity
//            $this->logBookingActivity($booking->id, 'cancel', [
//                'cancellation_type' => $request->cancellation_type,
//                'reason' => $request->cancellation_reason
//            ]);
//
//            DB::commit();
//
//            Log::info('✅ Booking Cancelled', [
//                'booking_id' => $booking->id,
//                'type' => $request->cancellation_type
//            ]);
//
//            return response()->json([
//                'success' => true,
//                'message' => 'Booking cancelled successfully.'
//            ]);
//
//        } catch (\Exception $e) {
//            DB::rollBack();
//            Log::error('Cancel Booking Error', ['error' => $e->getMessage()]);
//            throw $e;
//        }
//    }
//
//    //=======================================================
//    // HELPER METHODS
//    //=======================================================
//
//    /**
//     * Generate unique ticket number
//     */
//    private function generateTicketNumber()
//    {
//        return 'TKT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
//    }
//
//    /**
//     * Generate confirmation number
//     */
//    private function generateConfirmationNumber()
//    {
//        return 'CONF-' . strtoupper(substr(md5(uniqid()), 0, 8));
//    }
//
//    /**
//     * Log booking activity
//     */
//    private function logBookingActivity($bookingId, $action, $data = [])
//    {
//        try {
//            DB::table('booking_activity_logs')->insert([
//                'booking_id' => $bookingId,
//                'user_id' => auth()->id(),
//                'action' => $action,
//                'data' => json_encode($data),
//                'ip_address' => request()->ip(),
//                'user_agent' => request()->userAgent(),
//                'created_at' => Carbon::now(),
//                'updated_at' => Carbon::now()
//            ]);
//        } catch (\Exception $e) {
//            Log::warning('Failed to log booking activity', [
//                'error' => $e->getMessage(),
//                'booking_id' => $bookingId,
//                'action' => $action
//            ]);
//        }
//    }
//}


namespace Modules\Booking\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingCancel;
use Modules\Booking\Models\BookingCancellationRequest;
use Modules\Booking\Models\BookingPassenger;
use Modules\Booking\Models\BookingRefund;
use Modules\Booking\Models\BookingReissue;
use Modules\Booking\Models\Bookingroute;
use Modules\Booking\Models\BookingVoid;
use Modules\Booking\Models\BookingSSR;
use Modules\Flight\Models\Airline;

class NormalCheckoutController extends BookingController
{

    public function printTicket($code)
    {
        $booking = Booking::where('code', $code)->firstOrFail();

        // Check permission - শুধু নিজের booking print করতে পারবে
        if ($booking->customer_id != auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $passengers = BookingPassenger::where('booking_id', $booking->id)->get();
        $routes = BookingRoute::where('booking_id', $booking->id)
            ->orderBy('departure_at', 'asc')
            ->get();

        return view('Booking::frontend.ticket.ticket', compact('booking', 'passengers', 'routes'));
    }
    public function showInfo()
    {
        return view("Booking::frontend.normal-checkout.info");
    }

    public function confirmPayment(Request $request, $gateway)
    {
        $gateways = get_payment_gateways();
        if (empty($gateways[$gateway]) or !class_exists($gateways[$gateway])) {
            return $this->sendError(__("Payment gateway not found"));
        }
        $gatewayObj = new $gateways[$gateway]($gateway);
        if (!$gatewayObj->isAvailable()) {
            return $this->sendError(__("Payment gateway is not available"));
        }
        $res = $gatewayObj->confirmNormalPayment($request);
        $status = $res[0] ?? null;
        $message = $res[1] ?? null;
        $redirect_url = $res[2] ?? null;

        if (empty($redirect_url)) $redirect_url = route('gateway.info');

        return redirect()->to($redirect_url)->with($status ? "success" : "error", $message);
    }

    public function sendError($message, $data = [])
    {
        return redirect()->to(route('gateway.info'))->with('error', $message);
    }

    public function cancelPayment(Request $request, $gateway)
    {
        $gateways = get_payment_gateways();
        if (empty($gateways[$gateway]) or !class_exists($gateways[$gateway])) {
            return $this->sendError(__("Payment gateway not found"));
        }
        $gatewayObj = new $gateways[$gateway]($gateway);
        if (!$gatewayObj->isAvailable()) {
            return $this->sendError(__("Payment gateway is not available"));
        }
        $res = $gatewayObj->cancelNormalPayment($request);
        $status = $res[0] ?? null;
        $message = $res[1] ?? null;
        $redirect_url = $res[2] ?? null;

        if (empty($redirect_url)) $redirect_url = route('gateway.info');

        return redirect()->to($redirect_url)->with($status ? "success" : "error", $message);
    }

    //=======================================================
    // BOOKING ACTION HANDLER
    //=======================================================

    public function handleBookingAction(Request $request)
    {
//        return $request;
        Log::info('📥 Booking Action Request', [
            'data' => $request->all(),
            'action' => $request->action,
            'user_id' => auth()->id()
        ]);

        try {
            $action = $request->input('action');
            $bookingId = $request->input('booking_id');

            // Validate booking exists
            $booking = Booking::find($bookingId);
            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            // Route to appropriate handler
            switch ($action) {
                case 'issue':
                    return $this->handleIssueTicket($request, $booking);

                case 'void':
                    return $this->handleVoidTicket($request, $booking);

                case 'refund':
                    return $this->handleRefund($request, $booking);

                case 'reissue':
                    return $this->handleReissue($request, $booking);

                case 'passport':
                    return $this->handlePassportUpdate($request, $booking);

                case 'addssr':
                    return $this->handleAddSSR($request, $booking);

                case 'confirm':
                    return $this->handleConfirmBooking($request, $booking);

                case 'cancel':
                    return $this->handleCancelBooking($request, $booking);

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid action: ' . $action
                    ], 400);
            }

        } catch (\Exception $e) {
            Log::error('❌ Booking Action Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Handle Issue Ticket - Multiple Passengers
     */
    private function handleIssueTicket(Request $request, $booking)
    {
//        try {
//            $request->validate([
//                'passenger_ids' => 'required|array|min:1',
////                'passenger_ids.*' => 'required|exists:flight_passengers,id',
//                'pnr_number' => 'required|string',
//            ]);
//
//            DB::beginTransaction();
//
//            $passengerIds = $request->passenger_ids;
//            $issuedPassengers = [];
//            $alreadyIssued = [];
//
//            foreach ($passengerIds as $passengerId) {
//                $passenger = BookingPassenger::find($passengerId);
//
//                if ($passenger->ticket_number) {
//                    $alreadyIssued[] = $passenger->first_name . ' ' . $passenger->last_name;
//                    continue;
//                }
//
//                $ticketNumber = $this->generateTicketNumber();
//
//                $passenger->update([
//                    'ticket_number' => $ticketNumber,
//                    'booking_status' => 'issued',
//                    'ticket_issued_at' => Carbon::now()
//                ]);
//
//                $issuedPassengers[] = [
//                    'id' => $passenger->id,
//                    'name' => $passenger->first_name . ' ' . $passenger->last_name,
//                    'ticket_number' => $ticketNumber
//                ];
//            }
//
//            // Update booking PNR if not set
//            if (!$booking->pnr_id) {
//                $booking->pnr_id = $request->pnr_number;
//                $booking->save();
//            }
//
//            // Log activity
//            $this->logBookingActivity($booking->id, 'issue_request', [
//                'passenger_ids' => $passengerIds,
//                'issued_count' => count($issuedPassengers),
//                'already_issued_count' => count($alreadyIssued),
//                'passengers' => $issuedPassengers,
//                'notes' => $request->notes
//            ]);
//
//            DB::commit();
//
//            $message = count($issuedPassengers) > 0
//                ? 'Ticket issue requested for ' . count($issuedPassengers) . ' passenger(s).'
//                : 'All selected passengers already have tickets issued.';
//
//            if (count($alreadyIssued) > 0) {
//                $message .= ' Already issued: ' . implode(', ', $alreadyIssued);
//            }
//
//            return response()->json([
//                'success' => true,
//                'message' => $message,
//                'data' => [
//                    'issued_passengers' => $issuedPassengers,
//                    'already_issued' => $alreadyIssued
//                ]
//            ]);
//
//        } catch (\Exception $e) {
//            DB::rollBack();
//            Log::error('Issue Ticket Error', ['error' => $e->getMessage()]);
//            throw $e;
//        }
    }

    /**
     * ✅ Handle Void Ticket Request
     */
    private function handleVoidTicket(Request $request, $booking)
    {
        try {
            $request->validate([
                'pnr_number' => 'required|string',
                'reason' => 'required|string|min:10'
            ]);

            DB::beginTransaction();

            // Create void request (admin will approve)
            $void = BookingVoid::create([
                'booking_id' => $booking->id,
                'pnr' => $request->pnr_number,
                'void_charges' => 0, // Admin will set
                'status' => 'pending', // ✅ Pending for admin approval
                'reason' => $request->reason,
                'voided_by' => auth()->id(),
                'voided_at' => null ,// Will be set when approved
                'created_at' => now()
            ]);

            // Log activity
            $this->logBookingActivity($booking->id, 'void_request', [
                'void_id' => $void->id,
                'reason' => $request->reason,
                'status' => 'pending'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Void request submitted successfully. Admin will review and process.',
                'data' => [
                    'void_id' => $void->id,
                    'status' => 'pending'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Void Request Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * ✅ Handle Refund Request - Multiple Passengers
     */
    private function handleRefund(Request $request, $booking)
    {
        try {
            $request->validate([
                'passenger_ids' => 'required|array|min:1',
                'passenger_ids.*' => 'required',
                'pnr_number' => 'required|string',
                'refund_reason' => 'required|string|min:10'
            ]);

            DB::beginTransaction();

            // Check if "all" is selected
            $passengerIds = $request->passenger_ids;
            $isAllPassengers = in_array('all', $passengerIds);
            if ($isAllPassengers) {
                // Get all passengers
                $passengers = BookingPassenger::where('booking_id', $booking->id)->get();
                $passengerIds = $passengers->pluck('id')->toArray();
            } else {
                // Convert to integers
                $passengerIds = array_map('intval', $passengerIds);
                $passengers = BookingPassenger::whereIn('id', $passengerIds)
                    ->where('booking_id', $booking->id)
                    ->get();
            }

            // Build selected leg data from flight data
            $segmentData = [];
            $pnrRawData = is_string($booking->pnr_raw_data)
                ? json_decode($booking->pnr_raw_data, true)
                : (array)($booking->pnr_raw_data ?? []);
            $fdJourneys = $pnrRawData['journeys'] ?? [];
            $fdSegments = $pnrRawData['segments'] ?? [];
            $legsInput = $request->legs ?? [];

            if (!empty($fdJourneys) && !empty($legsInput)) {
                $segIndex = 0;
                foreach ($fdJourneys as $jIdx => $journey) {
                    $count = (int)($journey['number_of_flights'] ?? 1);
                    $selected = !empty($legsInput[$jIdx]['selected']);
                    if ($selected) {
                        $legSegs = array_slice($fdSegments, $segIndex, $count);
                        $segmentData[] = [
                            'type'           => $jIdx === 0 ? 'outbound' : 'return',
                            'label'          => ($journey['first_airport_code'] ?? '') . ' → ' . ($journey['last_airport_code'] ?? ''),
                            'date'           => $journey['departure_date'] ?? '',
                            'flight_count'   => $count,
                            'journey'        => $journey,
                            'segments'       => $legSegs,
                        ];
                    }
                    $segIndex += $count;
                }
            }

            // Create refund request
            $refund = BookingRefund::create([
                'booking_id' => $booking->id,
                'segment' => $segmentData,
                'passenger_id' => $passengerIds,
                'pnr' => $request->pnr_number,
                'refund_type' => $isAllPassengers ? 'full' : 'partial',
                'refund_amount' => 0, // ✅ Admin will calculate
                'refund_charges' => 0, // ✅ Admin will set
                'net_refund_amount' => 0, // ✅ Admin will calculate
                'status' => 'pending', // ✅ Pending for admin approval
                'reason' => $request->refund_reason,
                'requested_by' => auth()->id(),
                'requested_at' => Carbon::now()
            ]);

            // Log activity
            $this->logBookingActivity($booking->id, 'refund_request', [
                'refund_id' => $refund->id,
                'is_all_passengers' => $isAllPassengers,
                'passenger_ids' => $passengerIds,
                'passenger_count' => count($passengerIds),
                'segments' => $segmentData,
                'reason' => $request->refund_reason
            ]);

            DB::commit();

            $passengerNames = $passengers->pluck('first_name')->take(3)->join(', ');
            $message = 'Refund request submitted for ' . count($passengerIds) . ' passenger(s)';
            if (count($passengerIds) <= 3) {
                $message .= ': ' . $passengerNames;
            }

            return response()->json([
                'success' => true,
                'message' => $message . '. Admin will review and process.',
                'data' => [
                    'refund_id' => $refund->id,
                    'passenger_count' => count($passengerIds),
                    'segments' => $segmentData,
                    'status' => 'pending'
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Refund Request Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * ✅ Handle Reissue Request - Multiple Passengers
     */

    private function handleReissue(Request $request, $booking)
    {
    //    return $request;
        try {
            $request->validate([
                'passenger_ids'   => 'required|array|min:1',
                'passenger_ids.*' => 'required',
                'pnr_number'      => 'required|string',
                'legs'            => 'required|array|min:1',
                'remarks'         => 'nullable|string',
            ]);

            // ✅ Legs validation
            $legsInput = $request->legs ?? [];
            foreach ($legsInput as $i => $leg) {
                if (!empty($leg['selected'])) {
                    if (empty($leg['new_date'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Leg ' . ($i + 1) . ' এর জন্য নতুন date দিন।'
                        ], 422);
                    }
                    if (!\Carbon\Carbon::parse($leg['new_date'])->isFuture()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Leg ' . ($i + 1) . ' এর date অবশ্যই ভবিষ্যতের হতে হবে।'
                        ], 422);
                    }
                }
            }

            // ✅ Selected legs filter
            $selectedLegs = collect($legsInput)
                ->filter(fn($leg) => !empty($leg['selected']) && !empty($leg['new_date']))
                ->values();

            if ($selectedLegs->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'কমপক্ষে একটি leg select করুন।'
                ], 422);
            }

            // ✅ Round trip date order check
            if ($selectedLegs->count() == 2) {
                $dep = \Carbon\Carbon::parse($selectedLegs->get(0)['new_date']);
                $ret = \Carbon\Carbon::parse($selectedLegs->get(1)['new_date']);
                if ($ret->lte($dep)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Return date অবশ্যই departure date এর পরে হতে হবে।'
                    ], 422);
                }
            }

            // ✅ Trip type — leg count থেকে calculate
            $legCount = $selectedLegs->count();
            $tripType = match(true) {
                $legCount >= 3 => 'multi',
                $legCount == 2 => 'round',
                default        => 'oneway',
            };

            // ✅ Dates
            $newFlightDate = $selectedLegs->get(0)['new_date'];
            $returnDate    = $tripType === 'round' ? $selectedLegs->get(1)['new_date'] : null;

            // ✅ Airline code
            $airlineCode = $this->getAirlineCodeFromBooking($booking);
            // ✅ Supplier locator code from pnr_raw_data → store as airline_code
            $pnrRawData = is_string($booking->pnr_raw_data)
                ? json_decode($booking->pnr_raw_data, true)
                : (array)($booking->pnr_raw_data ?? []);
            $supplierPnr = $pnrRawData['supplier_locator']['supplier_code'] ?? '';

            DB::beginTransaction();

            // ✅ Passengers
            $passengerIds    = $request->passenger_ids;

            $isAllPassengers = in_array('all', $passengerIds);

            if ($isAllPassengers) {
                $passengers   = BookingPassenger::where('booking_id', $booking->id)->get();
                $passengerIds = $passengers->pluck('id')->toArray();
            } else {
                $passengerIds = array_map('intval', $passengerIds);
                $passengers   = BookingPassenger::whereIn('id', $passengerIds)
                    ->where('booking_id', $booking->id)
                    ->get();
            }

            // ✅ Old flight details
            $oldFlightDetails = [
                'trip_type'      => $booking->flight_type,
                'from'           => $booking->flight_from,
                'to'             => $booking->flight_to,
                'airline'        => $airlineCode,
                'departure_date' => $booking->start_date,
                'return_date'    => $booking->end_date,
            ];

            // ✅ New flight details — calculated values দিয়ে
            $newFlightDetails = [
                'trip_type'      => $tripType,
                'from'           => $booking->flight_from,
                'to'             => $booking->flight_to,
                'airline'        => $airlineCode,
                'departure_date' => $newFlightDate,
                'return_date'    => $returnDate,
                'legs'           => $selectedLegs->toArray(),
            ];

            // ✅ Reissue record
            $reissue = BookingReissue::create([
                'booking_id'         => $booking->id,
                'passenger_ids'      => $passengerIds,
                'old_pnr'            => $request->pnr_number,
                'new_pnr'            => null,
                'reissue_type'       => 'date',
                'reissue_charges'    => 0,
                'fare_difference'    => 0,
                'total_amount'       => 0,
                'status'             => 'pending',
                'old_flight_details' => $oldFlightDetails,
                'new_flight_details' => $newFlightDetails,
                'reason'             => $request->remarks,
                'requested_by'       => auth()->id(),
                'requested_at'       => Carbon::now(),
            ]);

            // ✅ Activity log
            $this->logBookingActivity($booking->id, 'reissue_request', [
                'reissue_id'        => $reissue->id,
                'is_all_passengers' => $isAllPassengers,
                'passenger_ids'     => $passengerIds,
                'passenger_count'   => count($passengerIds),
                'trip_type'         => $tripType,
                'legs'              => $selectedLegs->toArray(),
                'new_flight_date'   => $newFlightDate,
                'return_date'       => $returnDate,
            ]);

            DB::commit();

            // ✅ Original search params
            $originalSearchParams = json_decode($booking->search_params, true) ?? [];
            $originalSegments     = $originalSearchParams['segments'] ?? [];

            // ✅ Segments build — leg এর new_date দিয়ে, from/to original থেকে
            $segments = [];
            foreach ($selectedLegs as $i => $leg) {
                $segments[] = [
                    'from'      => $originalSegments[$i]['from'] ?? ($originalSegments[0]['from'] ?? ''),
                    'to'        => $originalSegments[$i]['to']   ?? ($originalSegments[0]['to']   ?? ''),
                    'departure' => $leg['new_date'],
                ];
            }

            // ✅ Passenger count by type
            $adultsCount   = 0;
            $childrenCount = 0;
            $infantsCount  = 0;
            $childrenAges  = [];

            foreach ($passengers as $passenger) {
                $type = strtoupper($passenger->traveler_type ?? 'ADULT');
                if ($type === 'ADULT') {
                    $adultsCount++;
                } elseif (in_array($type, ['CHILD', 'CNN', 'CHD'])) {
                    $childrenCount++;
                    if ($passenger->dob) {
                        $childrenAges[] = (string)\Carbon\Carbon::parse($passenger->dob)->age;
                    }
                } elseif ($type === 'INFANT') {
                    $infantsCount++;
                }
            }

            if ($childrenCount > 0 && empty($childrenAges)) {
                $childrenAges = array_map('strval', $originalSearchParams['children_ages'] ?? []);
            }

            // ✅ Search params — session format
            $searchParams = [
                'trip_type'     => $tripType,
                'adults'        => (string)($adultsCount > 0 ? $adultsCount : 1),
                'children'      => (string)$childrenCount,
                'infants'       => (string)$infantsCount,
                'travel_class'  => $originalSearchParams['travel_class'] ?? $booking->seat_class ?? 'ECONOMY',
                'children_ages' => $childrenAges,
                'segments'      => $segments,
                'airline_code'  => $supplierPnr,
            ];

            // ✅ Round trip এ return_date
            if ($tripType === 'round') {
                $searchParams['return_date'] = $returnDate;
            }

            // ✅ Session এর জন্য airline_codes সহ
            $searchParamsForSession = array_merge($searchParams, [
                'airline_codes' => $airlineCode ? [$airlineCode] : null,
            ]);

            // ✅ Passengers session data
            $passengersData = $passengers->map(fn($p) => [
                'id'                   => $p->id,
                'first_name'           => $p->first_name,
                'last_name'            => $p->last_name,
                'gender'               => $p->gender,
                'dob'                  => $p->dob,
                'email'                => $p->email,
                'phone'                => $p->phone,
                'passport_number'      => $p->passport_number,
                'passport_expiry_date' => $p->passport_expiry_date,
                'country'              => $p->country,
                'traveler_type'        => $p->traveler_type ?? 'ADULT',
                'nationality'          => $p->nationality ?? $p->country,
            ])->toArray();

            // ✅ Session store
            session([
                'reissue_data' => [
                    'reissue_id'     => $reissue->id,
                    'old_booking_id' => $booking->id,
                    'old_pnr'        => $request->pnr_number,
                    'airline_code'   => $supplierPnr,
                    'passenger_ids'  => $passengerIds,
                    'passengers'     => $passengersData,
                    'search_params'  => $searchParamsForSession,
                    'remarks'        => $request->remarks,
                    'legs'           => $selectedLegs->toArray(),
                ],
                'reissue_search_params' => $searchParamsForSession,
            ]);

            Log::info('✅ Reissue Request Created', [
                'reissue_id'      => $reissue->id,
                'booking_id'      => $booking->id,
                'trip_type'       => $tripType,
                'leg_count'       => $legCount,
                'passenger_count' => count($passengerIds),
                'search_params'   => $searchParams,
            ]);

            return response()->json([
                'success'      => true,
                'message'      => 'Reissue request submitted. Redirecting to flight search...',
                'redirect_url' => route('flight.search', $searchParams),
                'data'         => [
                    'reissue_id'      => $reissue->id,
                    'passenger_count' => count($passengerIds),
                    'trip_type'       => $tripType,
                    'legs'            => $selectedLegs->toArray(),
                    'status'          => 'pending',
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Reissue Request Error', [
                'error'      => $e->getMessage(),
                'booking_id' => $booking->id ?? null,
            ]);
            throw $e;
        }
    }

    /**
     * ✅ Helper: Get airline code from booking
     */
    private function getAirlineCodeFromBooking($booking)
    {
        try {
            // Get airline name from booking
            $airlineName = $booking->airline;

            if (!$airlineName) {
                return null;
            }

            // If already a code (2 letters), return it
            if (strlen($airlineName) === 2) {
                return strtoupper($airlineName);
            }

            // ✅ Get airline code from airlines table
            $airline = Airline::where('name', 'LIKE', '%' . $airlineName . '%')
                ->orWhere('designator', $airlineName)
                ->first();

            if ($airline) {
                return $airline->designator ;
            }

            // ✅ Fallback: Try to extract from flight_data JSON
            if ($booking->flight_data) {
                $flightData = json_decode($booking->flight_data, true);
                if (isset($flightData['validatingCarrier'])) {
                    return $flightData['validatingCarrier'];
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::warning('Failed to get airline code', [
                'booking_id' => $booking->id,
                'airline_name' => $booking->airline,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * ✅ Handle Passport Update (Single passenger)
     */
    private function handlePassportUpdate(Request $request, $booking)
    {
//        return $request;
        try {
            $request->validate([
                'passenger_id' => 'required|exists:flight_passengers,id',
                'passport_number' => 'required|string',
                'expiry_date' => 'required|date|after:today',
                'issuing_country' => 'nullable|string'
            ]);

            $passenger = BookingPassenger::find($request->passenger_id);

            $passenger->update([
                'passport_number' => $request->passport_number,
                'passport_expiry_date' => $request->expiry_date,
                'country' => $request->issuing_country ?? $passenger->country
            ]);

            // Log activity
            $this->logBookingActivity($booking->id, 'passport_update', [
                'passenger_id' => $passenger->id,
                'passenger_name' => $passenger->first_name . ' ' . $passenger->last_name,
                'passport_number' => $request->passport_number
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Passport updated for ' . $passenger->first_name . ' ' . $passenger->last_name
            ]);

        } catch (\Exception $e) {
            Log::error('Passport Update Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * ✅ Handle Add SSR Request (Single passenger)
     */private function handleAddSSR(Request $request, $booking)
{
    try {
        $request->validate([
            'passenger_id' => 'required',
            'ssr_type'     => 'required|in:meal,wheelchair,baggage,seat,infant,medical,other',
            'ssr_details'  => 'required|string',
            'pnr_number'   => 'nullable|string',
        ]);

        DB::beginTransaction();

        $passenger = BookingPassenger::where('id', $request->passenger_id)
            ->where('booking_id', $booking->id)
            ->first();

        if (!$passenger) {
            return response()->json([
                'success' => false,
                'message' => 'Passenger not found for this booking.'
            ], 422);
        }
        // ✅ SSR code map
        $ssrCodeMap = [
            'meal'        => 'SPML',
            'wheelchair'  => 'WCHR',
            'baggage'     => 'XBAG',
            'seat'        => 'SEAT',
            'infant'      => 'BSCT',
            'medical'     => 'MEDA',
            'other'       => 'OTHS',
        ];

        // ✅ SSR details এ থেকে code extract (যদি user নির্দিষ্ট code দেয়)
        $ssrCode = $ssrCodeMap[$request->ssr_type] ?? 'OTHS';
        $details = $request->ssr_details;

        // Meal code detect
        if ($request->ssr_type === 'meal') {
            $mealCodes = ['VGML','MOML','KSML','DBML','LFML','CHML','BBML','SFML'];
            foreach ($mealCodes as $code) {
                if (str_contains(strtoupper($details), $code)) {
                    $ssrCode = $code;
                    break;
                }
            }
        }

        // Wheelchair code detect
        if ($request->ssr_type === 'wheelchair') {
            foreach (['WCHR','WCHS','WCHC'] as $code) {
                if (str_contains(strtoupper($details), $code)) {
                    $ssrCode = $code;
                    break;
                }
            }
        }

        // ✅ Create SSR
        $ssr = BookingSSR::create([
            'booking_id'        => $booking->id,
            'passenger_id'      => $passenger->id,
            'ssr_type'          => $request->ssr_type,
            'ssr_code'          => $ssrCode,
            'description'       => $details,
            'amount'            => 0,
            'status'            => 'pending',
            'airline_reference' => null,
            'ssr_details'       => [
                'type'        => $request->ssr_type,
                'code'        => $ssrCode,
                'details'     => $details,
                'pnr'         => $request->pnr_number ?? ($booking->pnr_id ?? null),
                'airline'     => $booking->flight_from ?? null,
                'requested_at'=> now()->toDateTimeString(),
            ],
            'added_by'  => auth()->id(),
        ]);

        // ✅ Activity log
        $this->logBookingActivity($booking->id, 'ssr_request', [
            'ssr_id'           => $ssr->id,
            'passenger_id'     => $passenger->id,
            'passenger_name'   => $passenger->first_name . ' ' . $passenger->last_name,
            'ssr_type'         => $request->ssr_type,
            'ssr_code'         => $ssrCode,
        ]);

        DB::commit();

        // ✅ Type label map
        $typeLabels = [
            'meal'       => 'Meal Preference',
            'wheelchair' => 'Wheelchair Assistance',
            'baggage'    => 'Extra Baggage',
            'seat'       => 'Seat Preference',
            'infant'     => 'Infant Service',
            'medical'    => 'Medical Assistance',
            'other'      => 'Special Request',
        ];

        return response()->json([
            'success' => true,
            'message' => ($typeLabels[$request->ssr_type] ?? 'SSR') . ' request submitted for '
                . $passenger->first_name . ' ' . $passenger->last_name
                . '. Admin will review and confirm.',
            'data' => [
                'ssr_id'     => $ssr->id,
                'ssr_code'   => $ssrCode,
                'ssr_type'   => $request->ssr_type,
                'status'     => 'pending',
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('SSR Request Error', [
            'error'      => $e->getMessage(),
            'booking_id' => $booking->id ?? null,
        ]);
        throw $e;
    }
}

    /**
     * ✅ Handle Confirm Booking
     */
    private function handleConfirmBooking(Request $request, $booking)
    {
//        return 'sarowar';
        try {
//            if ($booking->status !== 'processing') {
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Only processing bookings can be confirmed.'
//                ], 400);
//            }

            $booking->update([
                'status' => 'confirmed',
//                'confirmation_number' => $request->confirmation_number ?? $this->generateConfirmationNumber(),
//                'confirmation_notes' => $request->confirmation_notes,
                'confirmed_at' => Carbon::now()
            ]);

            BookingPassenger::where('booking_id', $booking->id)
                ->update(['status' => 'confirmed']);

            $this->logBookingActivity($booking->id, 'confirm', [
                'confirmation_number' => $booking->confirmation_number,
                'notes' => $request->confirmation_notes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking confirmed successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Confirm Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * ✅ Handle Cancel Booking
     */
    private function handleCancelBooking(Request $request, $booking)
    {
//        return $request;
        try {
            if ($booking->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking already cancelled.'
                ], 400);
            }

            // Check if already has pending request
//            if ($booking->cancellationRequest && $booking->cancellationRequest->status === 'pending') {
//                return response()->json([
//                    'success' => false,
//                    'message' => 'Cancellation request already submitted and pending review.'
//                ], 400);
//            }

            $request->validate([
                'cancellation_type' => 'required|in:customer_request,no_show,flight_cancelled,other',
                'cancellation_reason' => 'required|string|min:10'
            ]);

            DB::beginTransaction();

            // Create cancellation request
            BookingCancel::create([
                'booking_id' => $booking->id,
                'user_id' => auth()->id(),
                'cancellation_type' => $request->cancellation_type,
                'cancellation_reason' => $request->cancellation_reason,
                'status' => 'pending',
                'created_at' => Carbon::now()
            ]);

            // Update booking status to pending_cancellation
//            $booking->update(['status' => 'pending_cancellation']);

            $this->logBookingActivity($booking->id, 'cancellation_requested', [
                'cancellation_type' => $request->cancellation_type,
                'reason' => $request->cancellation_reason
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cancellation request submitted successfully. Admin will review shortly.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cancel Request Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    //=======================================================
    // HELPER METHODS
    //=======================================================

    private function generateTicketNumber()
    {
        return 'TKT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    private function generateConfirmationNumber()
    {
        return 'CONF-' . strtoupper(substr(md5(uniqid()), 0, 8));
    }

    private function logBookingActivity($bookingId, $action, $data = [])
    {
        try {
            DB::table('booking_activity_logs')->insert([
                'booking_id' => $bookingId,
                'user_id' => auth()->id(),
                'action' => $action,
                'data' => json_encode($data),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to log activity', [
                'error' => $e->getMessage(),
                'booking_id' => $bookingId,
                'action' => $action
            ]);
        }
    }
}
