<?php

namespace Modules\Booking\Controllers;


use Illuminate\Support\Facades\Request;
use Modules\Booking\Models\BookingCancel;
use Modules\Booking\Models\BookingRefund;
use Modules\Booking\Models\BookingReissue;
use Modules\Booking\Models\BookingSsr;
use Modules\Booking\Models\BookingVoid;

class BookingRequestsController extends BookingController
{
    // Void Requests
    public function voidIndex()
    {
        $requests = BookingVoid::where('voided_by', auth()->id())
//            ->with('booking')
            ->orderBy('created_at', 'desc')
            ->get(); // paginate(10) এর বদলে get()
//return $requests;
        return view('Booking::frontend.requests.void', compact('requests'));
    }

    public function voidApprove($id)
    {
        $voidRequest = BookingVoid::where('id', $id)
            ->where('voided_by', auth()->id())
            ->firstOrFail();

        if ($voidRequest->status !== 'waiting_user_approval') {
            return redirect()->back()->with('error', 'This request is not waiting for your approval');
        }

        $voidRequest->update([
            'status' => 'user_approved',
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Void request approved successfully');
    }

    public function voidReject($id)
    {
        $voidRequest = BookingVoid::where('id', $id)
            ->where('voided_by', auth()->id())
            ->firstOrFail();

        if ($voidRequest->status !== 'waiting_user_approval') {
            return redirect()->back()->with('error', 'This request is not waiting for your approval');
        }

        $voidRequest->update([
            'status' => 'user_rejected',
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Void request rejected successfully');
    }
    // Refund Requests
    public function refundIndex()
    {
        $requests = BookingRefund::where('requested_by', auth()->id())
            ->with('booking')
            ->orderBy('created_at', 'desc')
            ->get(); // paginate(10) সরিয়ে get() — DataTables নিজেই pagination handle করে

        return view('Booking::frontend.requests.refund', compact('requests'));
    }

    public function refundApprove($id)
    {
        $refundRequest = BookingRefund::where('id', $id)
            ->where('requested_by', auth()->id())
            ->firstOrFail();

        if ($refundRequest->status !== 'waiting_user_approval') {
            return redirect()->back()->with('error', 'This request is not waiting for your approval');
        }

        $refundRequest->update([
            'status' => 'user_approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Refund request approved successfully');
    }

    public function refundReject($id)
    {
        $refundRequest = BookingRefund::where('id', $id)
            ->where('requested_by', auth()->id())
            ->firstOrFail();

        if ($refundRequest->status !== 'waiting_user_approval') {
            return redirect()->back()->with('error', 'This request is not waiting for your approval');
        }

        $refundRequest->update([
            'status' => 'user_rejected',
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Refund request rejected successfully');
    }

    // Reissue Requests
    public function reissueIndex()
    {
        $requests = BookingReissue::where('requested_by', auth()->id())
            ->with('booking')
            ->orderBy('created_at', 'desc')
            ->get(); // paginate(10) সরিয়ে get() — DataTables নিজেই pagination handle করে

        return view('Booking::frontend.requests.reissue', compact('requests'));
    }

    public function reissueApprove($id)
    {
        $reissueRequest = BookingReissue::where('id', $id)
            ->where('requested_by', auth()->id())
            ->firstOrFail();

        if ($reissueRequest->status !== 'waiting_user_approval') {
            return redirect()->back()->with('error', 'This request is not waiting for your approval');
        }

        $reissueRequest->update([
            'status' => 'user_approved',
            'processed_at' => now(),
            'processed_by' => auth()->id(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Reissue request approved successfully');
    }

    public function reissueReject($id)
    {
        $reissueRequest = BookingReissue::where('id', $id)
            ->where('requested_by', auth()->id())
            ->firstOrFail();

        if ($reissueRequest->status !== 'waiting_user_approval') {
            return redirect()->back()->with('error', 'This request is not waiting for your approval');
        }

        $reissueRequest->update([
            'status' => 'user_rejected',
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Reissue request rejected successfully');
    }

    // Cancel Requests
    public function cancelIndex()
    {
        $requests = BookingCancel::where('user_id', auth()->id())
            ->with('booking')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('Booking::frontend.requests.cancle', compact('requests'));
    }
    // SSR Requests
    public function ssrIndex()
    {
        $requests = BookingSsr::where('added_by', auth()->id())
            ->with('booking')
            ->orderBy('created_at', 'desc')
            ->get(); // paginate(10) সরিয়ে get() — DataTables নিজেই pagination handle করে

        return view('Booking::frontend.requests.add_ssr', compact('requests'));
    }

    public function ssrApprove($id)
    {
        $ssrRequest = BookingSsr::where('id', $id)
            ->where('added_by', auth()->id())
            ->firstOrFail();

        if ($ssrRequest->status !== 'waiting_user_approval') {
            return redirect()->back()->with('error', 'This request is not waiting for your approval');
        }

        $ssrRequest->update([
            'status' => 'user_approved',
            'confirmed_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'SSR request approved successfully');
    }

    public function ssrReject($id)
    {
        $ssrRequest = BookingSsr::where('id', $id)
            ->where('added_by', auth()->id())
            ->firstOrFail();

        if ($ssrRequest->status !== 'waiting_user_approval') {
            return redirect()->back()->with('error', 'This request is not waiting for your approval');
        }

        $ssrRequest->update([
            'status' => 'user_rejected',
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'SSR request rejected successfully');
    }
}
