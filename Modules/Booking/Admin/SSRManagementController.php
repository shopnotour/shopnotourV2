<?php

namespace Modules\Booking\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Booking\Models\Booking;
use Modules\Booking\Models\BookingSsr;
use Modules\User\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SSRManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Admin permission check - আপনার system অনুযায়ী adjust করুন
//        $this->middleware('permission:booking_ssr');
    }

    /**
     * SSR List - সব SSR requests দেখাবে
     */
//    public function index(Request $request)
//    {
//        $query = BookingSsr::with(['booking.user', 'passenger', 'addedBy'])
//            ->orderBy('created_at', 'desc');
//
//        // Status filter
//        if ($request->filled('status')) {
//            $query->ofStatus($request->status);  // ✅ Scope ব্যবহার
//        }
//
//        // SSR Type filter
//        if ($request->filled('ssr_type')) {
//            $query->ofType($request->ssr_type);  // ✅ Scope ব্যবহার
//        }
//
//        // Search by booking id or text
//        if ($request->filled('s')) {
//            $search = $request->s;
//            $query->where(function ($q) use ($search) {
//                $q->where('booking_id', $search)
//                    ->orWhere('ssr_code', 'like', "%{$search}%")
//                    ->orWhere('airline_reference', 'like', "%{$search}%")
//                    ->orWhereHas('passenger', function ($pq) use ($search) {
//                        $pq->where('first_name', 'like', "%{$search}%")
//                            ->orWhere('last_name', 'like', "%{$search}%");
//                    });
//            });
//        }
//
//        $rows = $query->paginate(20);  // ✅ $rows নাম দিন (view এ $rows ব্যবহার করা হয়েছে)
//
//        $data = [
//            'rows' => $rows,
//            'page_title' => __('SSR Management'),
//            'breadcrumbs' => [
//                ['name' => __('SSR Management'), 'class' => 'active']
//            ]
//        ];
//
//        return view('Booking::admin.AddSSR.index', $data);
//    }

    public function index(Request $request)
    {
        $rows = BookingSsr::with(['booking.user', 'passenger', 'addedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Booking::admin.AddSSR.index', compact('rows'));
    }
    /**
     * Single SSR details দেখাবে
     */
    public function show($id)
    {
        $ssr = BookingSSR::with(['booking', 'booking.customer', 'passenger'])
            ->findOrFail($id);

        $data = [
            'ssr' => $ssr,
            'page_title' => __('SSR Details'),
            'breadcrumbs' => [
                ['name' => __('SSR Management'), 'url' => route('admin.ssrs.index')],
                ['name' => __('SSR Details'), 'class' => 'active']
            ]
        ];

        return view('Flight::admin.ssr.show', $data);
    }

    /**
     * Admin amount set করবে এবং user approval এর জন্য পাঠাবে
     */
    public function setAmount(Request $request, $id)
    {
        $request->validate([
            'available' => 'required|in:yes,no',
            'amount' => 'required_if:available,yes|numeric|min:0',
            'description' => 'nullable|string|max:500',
        ]);

        $ssr = BookingSsr::findOrFail($id);

        if ($ssr->status !== 'pending') {
            return response()->json([
                'status' => false,
                'message' => __('এই SSR টি আর pending নেই।')
            ], 400);
        }

        // যদি available না হয়, reject করুন
        if ($request->available === 'no') {
            $ssr->update([
                'status' => 'failed',
                'description' => $request->description ?? 'SSR not available',
                'updated_by' => Auth::id(),
                'confirmed_at' => now(),
            ]);

            return response()->json([
                'status' => true,
                'message' => __('SSR not available হিসেবে mark করা হয়েছে।')
            ]);
        }

        // Available হলে amount সেট করুন
        $ssr->update([
            'amount' => $request->amount,
            'description' => $request->description,
            'status' => 'waiting_user_approval',
            'updated_by' => Auth::id(),
            'confirmed_at' => now(),
        ]);

        // User কে notification পাঠান (optional)
        // $this->sendUserNotification($ssr, 'amount_set');

        return response()->json([
            'status' => true,
            'message' => __('Amount সেট করা হয়েছে। User এর approval এর জন্য অপেক্ষা করুন।')
        ]);
    }

    /**
     * Final Approve - User approve করার পর Admin final approve করবে
     * Wallet থেকে টাকা কাটবে
     */
    public function approve(Request $request, $id)
    {
        $ssr = BookingSsr::with(['booking', 'booking.user'])->findOrFail($id);

        // Check: User আগে approve করেছে কিনা
        if ($ssr->status !== 'user_approved') {
            return response()->json([
                'status' => false,
                'message' => __('User এখনো approve করেনি।')
            ], 400);
        }

        $customer = $ssr->booking->user;
        $amount = $ssr->amount;

        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => __('Customer পাওয়া যায়নি।')
            ], 400);
        }

        // Wallet balance check
        if ($customer->credit_balance < $amount) {
            return response()->json([
                'status' => false,
                'message' => __('User এর wallet এ পর্যাপ্ত balance নেই। প্রয়োজন: ৳' . number_format($amount, 2) . ', আছে: ৳' . number_format($customer->credit_balance, 2))
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Wallet থেকে টাকা কাটুন
            $customer->credit_balance -= $amount;
            $customer->save();

            // SSR status update
            $ssr->update([
                'status' => 'confirmed',
                'airline_reference' => $request->airline_reference,
                'updated_by' => Auth::id(),
                'confirmed_at' => now(),
            ]);

            // Booking total update (optional)
            $booking = $ssr->booking;
            $booking->total += $amount;
            $booking->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => __('SSR approved এবং ৳' . number_format($amount, 2) . ' wallet থেকে কাটা হয়েছে।')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => __('কিছু সমস্যা হয়েছে: ' . $e->getMessage())
            ], 500);
        }
    }

    /**
     * Reject SSR
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:5|max:500',
        ]);

        $ssr = BookingSsr::findOrFail($id);

        $ssr->update([
            'status' => 'failed',
            'description' => $request->rejection_reason,
            'updated_by' => Auth::id(),
            'confirmed_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => __('SSR reject করা হয়েছে।')
        ]);
    }

    /**
     * Bulk Action - একসাথে অনেক SSR handle
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:booking_ssrs,id',
            'action' => 'required|in:approve,reject',
        ]);

        $count = 0;

        foreach ($request->ids as $id) {
            $ssr = BookingSSR::find($id);

            if ($request->action === 'approve' && $ssr->status === 'user_approved') {
                // Approve logic (simplified)
                $customer = $ssr->booking->customer;
                $walletBalance = $this->getWalletBalance($customer->id);

                if ($walletBalance >= $ssr->amount) {
                    DB::transaction(function () use ($ssr, $customer) {
                        $this->deductFromWallet($customer->id, $ssr->amount, $ssr);
                        $ssr->update(['status' => 'confirmed', 'confirmed_at' => now()]);
                    });
                    $count++;
                }
            } elseif ($request->action === 'reject' && $ssr->status === 'pending') {
                $ssr->update(['status' => 'failed']);
                $count++;
            }
        }

        return back()->with('success', __($count . ' টি SSR ' . $request->action . ' করা হয়েছে।'));
    }


}
