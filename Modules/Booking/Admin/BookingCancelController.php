<?php

namespace Modules\Booking\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Modules\Booking\Models\BookingCancel;
use Modules\Booking\Models\BookingPassenger;

class BookingCancelController extends Controller
{public function index()
{
    $requests = BookingCancel::with(['booking', 'user', 'reviewer'])
        ->orderBy('status', 'asc')
        ->orderBy('created_at', 'desc')
        ->get();

    return view('Booking::admin.cancel.index', compact('requests'));
}

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string'
        ]);

        $cancellationRequest = BookingCancel::findOrFail($id);

        $cancellationRequest->update([
            'status' => $request->status,
            'reviewed_by' => auth()->id(),
            'admin_note' => $request->admin_note,
            'reviewed_at' => Carbon::now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Request ' . $request->status . ' successfully!'
        ]);
    }
}
