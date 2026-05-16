<?php
namespace Modules\User\Admin;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\AdminController;
use Modules\Booking\Models\Payment;
use Modules\User\Events\UpdateCreditPurchase;
use Modules\User\Models\Wallet\DepositPayment;
use Modules\Media\Traits\HasUpload;
use Modules\User\Models\Wallet\Transaction;

class WalletController extends AdminController
{
   use HasUpload;

    public function creditList(Request $request, $user_id = '')
    {
        $user  = User::findOrFail($user_id);

        $dateFrom = $request->input('date_from', date('Y-m-d'));
        $dateTo   = $request->input('date_to',   date('Y-m-d'));

        // Opening Balance
        $openingReceived = Transaction::whereIn('type', ['deposit', 'topup'])
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereDate('created_at', '<', $dateFrom)
            ->sum('amount');

        $openingPaid = Transaction::where(function($q) {
            $q->whereIn('type', ['withdraw', 'debit', 'payment', 'credit'])
                ->orWhereIn('status', ['refund', 'void', 'voided', 'refunded']);
        })
            ->whereDate('created_at', '<', $dateFrom)
            ->sum('amount');

        $openingBalance = $openingReceived - $openingPaid;

        // Filtered rows
        $query = Transaction::where('user_id', $user_id)
            ->with(['author', 'creator', 'updater'])
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->orderBy('id', 'DESC');

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('type'))   $query->where('type',   $request->type);

        $rows = $query->get();

        // Stats
        $depositConfirmedCount   = $rows->where('type', 'deposit')->where('status', 'confirmed')->count();
        $depositConfirmedAmount  = $rows->where('type', 'deposit')->where('status', 'confirmed')->sum('amount');
        $depositPendingCount     = $rows->where('type', 'deposit')->where('status', 'pending')->count();
        $depositPendingAmount    = $rows->where('type', 'deposit')->where('status', 'pending')->sum('amount');
        $withdrawConfirmedCount  = $rows->where('type', 'withdraw')->where('status', 'confirmed')->count();
        $withdrawConfirmedAmount = $rows->where('type', 'withdraw')->where('status', 'confirmed')->sum('amount');
        $withdrawPendingCount    = $rows->where('type', 'withdraw')->where('status', 'pending')->count();
        $withdrawPendingAmount   = $rows->where('type', 'withdraw')->where('status', 'pending')->sum('amount');
        $paymentCount  = $rows->where('type', 'debit')->where('status', 'payment')->count();
        $paymentAmount = $rows->where('type', 'debit')->where('status', 'payment')->sum('amount');
        $refundCount   = $rows->where('type', 'credit')->count();
        $refundAmount  = $rows->where('type', 'credit')->sum('amount');

        // Period
        $periodReceived = $rows
            ->whereIn('type', ['deposit', 'topup'])
            ->whereIn('status', ['confirmed', 'completed'])
            ->sum('amount');

        $periodPaid = $rows->filter(function ($row) {
            return in_array($row->type, ['withdraw', 'debit', 'payment', 'credit'])
                || in_array($row->status, ['refund', 'void', 'voided', 'refunded']);
        })->sum('amount');

        $closingBalance = $openingBalance + $periodReceived - $periodPaid;

        $statuses = Transaction::where('user_id', $user_id)
            ->whereNotNull('status')->distinct()->pluck('status');

        return view("User::admin.wallet.credit_list", compact(
            'rows', 'user', 'statuses', 'dateFrom', 'dateTo',
            'openingBalance', 'periodReceived', 'periodPaid', 'closingBalance',
            'depositConfirmedCount', 'depositConfirmedAmount',
            'depositPendingCount',   'depositPendingAmount',
            'withdrawConfirmedCount', 'withdrawConfirmedAmount',
            'withdrawPendingCount',   'withdrawPendingAmount',
            'paymentCount', 'paymentAmount',
            'refundCount',  'refundAmount'
        ));
    }

   public function status_filter(Request $request)
   {
       $trans=Transaction::where('status',$request->status)->with('author')->orderBy('id','DESC');
       return view("User::admin.wallet.all_topup_transacton",[
           'rows'=>$trans->paginate(20),
           'page_title'=>__("Credit Transactions"),
           'breadcrumbs'=>[
               [
                   'url'=>route('user.admin.index'),
                   'name'=>__("Users"),
               ],
               [
                   'url'=>'#',
                   'name'=>__('Credit transaction list'),
               ],
           ]
       ]);
   }

    public function transactions(Request $request)
    {
        $dateFrom = $request->input('date_from', date('Y-m-d'));
        $dateTo   = $request->input('date_to',   date('Y-m-d'));

        // ── Filtered rows ────────────────────────────────────────────
        $query = Transaction::with(['author', 'creator', 'updater'])
            ->whereIn('type', ['deposit', 'withdraw', 'debit', 'credit'])
            ->whereDate('created_at', '>=', $dateFrom)
            ->whereDate('created_at', '<=', $dateTo)
            ->orderBy('id', 'DESC');

        $rows = $query->get();

        // ── Ajax response ─────────────────────────────────────────────
        if ($request->ajax()) {
            $rowsData = $rows->map(function($row) {
                $isDeposit  = in_array($row->type, ['deposit','topup']);
//                    && in_array($row->status, ['confirmed','completed','pending']);
                $isWithdraw = in_array($row->type, ['withdraw','debit','payment','credit']);
//                    || in_array($row->status, ['refund','void','voided','refunded','pending']);
                return [
                    'id'               => $row->id,
                    'user_id'          => $row->user_id,
                    'author_name'      => $row->author->name ?? 'N/A',
                    'author_phone'     => $row->author->phone ?? '',
                    'author_email'     => $row->author->email ?? '',
                    'deposit_date' => $row->deposit_date ?? \Carbon\Carbon::parse($row->created_at)->format('Y-m-d'),
                    'amount'           => $row->amount,
                    'amount_formatted' => number_format($row->amount, 2),
                    'type'             => $row->type,
                    'status'           => $row->status,
                    'transaction_type' => $row->transaction_type ?? '',
                    'reference'        => $row->reference ?? '',
                    'remarks'          => $row->remarks ?? '',
                    'creator_name'     => $row->creator->name ?? '—',
                    'updater_name'     => $row->update_user ? ($row->updater->name ?? '—') : '—',
                    'attachment_id'    => $row->attachment_id,
                    'attachment_url'   => $row->attachment_id
                        ? \Modules\Media\Helpers\FileHelper::url($row->attachment_id, 'full')
                        : null,
                    'attachment_thumb' => $row->attachment_id
                        ? \Modules\Media\Helpers\FileHelper::url($row->attachment_id, 'thumb')
                        : null,
                    'is_deposit'       => $isDeposit,
                    'is_withdraw'      => $isWithdraw,
                    'amount_words' => \App\Helpers\AmountHelper::inWords($row->amount),
                ];
            });

            return response()->json(['rows' => $rowsData]);
        }

        return view("User::admin.wallet.all_topup_transacton", compact('rows', 'dateFrom', 'dateTo'));
    }

    public function statusUpdate($trans_id = '')
    {
        $trans = Transaction::findOrFail($trans_id);
        $user  = User::findOrFail($trans->user_id);

        // ❗ already confirmed হলে stop
        if ($trans->status === 'confirmed') {
            return back()->with('error', 'Already processed');
        }

        $balanceBefore = (float) $user->credit_balance;

        // =========================
        // 🟢 DEPOSIT
        // =========================
        if ($trans->type === 'deposit') {

            $balanceAfter = $balanceBefore + (float) $trans->amount;
        }

        // =========================
        // 🔴 WITHDRAW
        // =========================
        else {

            // ❗ extra safety check
            if ($trans->amount > $balanceBefore) {
                return back()->with('error', 'Insufficient balance');
            }

            $balanceAfter = $balanceBefore - (float) $trans->amount;
        }

        // ✅ update user balance
        $user->credit_balance = $balanceAfter;
        $user->save();

        // ✅ update transaction
        $trans->status = 'confirmed';
        $trans->meta = [
            'balance_before' => $balanceBefore,
            'balance_after'  => $balanceAfter,
            'payment_method' => $trans->transaction_type,
            'approved_by'    => auth()->id(),
        ];
        $trans->save();

        return back()->with("success", "Transaction approved successfully");
    }

//    public function statusUpdate($trans_id = '')
//    {
//        $trans = Transaction::find($trans_id);
//
//        $user = User::find($trans->user_id);
//
//        $balanceBefore = (float) $user->credit_balance;
//        $balanceAfter  = $balanceBefore + (float) $trans->amount;
//
//        // ✅ User balance update
//        $user->credit_balance = $balanceAfter;
//        $user->save();
//
//        // ✅ Transaction confirm + meta update
//        $trans->status = 'confirmed';
//        $trans->meta   = json_encode([
//            'balance_before' => $balanceBefore,
//            'balance_after'  => $balanceAfter,
//            'payment_method'  => $trans->transaction_type,
//        ]);
//        $trans->save();
//
//        return redirect()->back()->with("success", __(":amount credit added Successfully"));
//    }
    public function addCredit($user_id = ''){
        if(empty($user_id)){
            abort(404);
        }
        $row = User::find($user_id);
        if(!$row){
            abort(404);
        }

        $data = [
            'row'=>$row,
            'page_title'=>__("Add Credit"),
            'breadcrumbs'=>[
                [
                    'url'=>route('user.admin.index'),
                    'name'=>__("Users"),
                ],
                [
                    'url'=>'#',
                    'name'=>__('Add credit for :name',['name'=>$row->display_name]),
                ],
            ]
        ];
        return view("User::admin.wallet.add-credit",$data);
    }
//
//    public function store(Request $request,$user_id = ''){
//        if(empty($user_id)){
//            abort(404);
//        }
//        $row = User::find($user_id);
//        if(!$row){
//            abort(404);
//        }
//        $amount = request()->input('credit_amount',0);
//
//        if($amount){
//            try {
//                $attachmentId = null;
//
//                if($request->hasFile('attachment_id')){
//                    $media = $this->uploadFile(
//                        $request,
//                        'attachment_id',
//                        'image',
//                        0
//                    );
//                    $attachmentId = $media->id;
//                }
//
//                // ✅ Deposit এর আগে ও পরের balance
//                $balanceBefore = (float) $row->credit_balance;
//                $balanceAfter  = $balanceBefore + (float) $amount;
//
//                $row->deposit($amount,[
//                    'user_id'=>$user_id,
//                    'admin_deposit'=>auth()->id(),
//                    'reference'=>$request->reference,
//                    'transaction_type'=>$request->transaction_type,
//                    'deposit_date'=>$request->deposit_date,
//                    'remarks'=>$request->remarks,
//                    'attachment_id'=>$attachmentId,
//                    'meta'             => json_encode([
//                        'balance_before' => $balanceBefore,
//                        'balance_after'  => $balanceAfter,
//                        'payment_method'  => $request->transaction_type,
//                    ]),
//                ]);
//            }catch (\Exception $exception){
//                return redirect()->back()->with("error",$exception->getMessage());
//            }
//
//            return redirect()->back()->with("success",__(":amount credit added",['amount'=>$amount]));
//        }
//    }

    public function store(Request $request, $user_id = '')
    {
        if (empty($user_id)) abort(404);

        $row = User::find($user_id);
        if (!$row) abort(404);

        $amount = (float) $request->input('credit_amount', 0);
        $type   = $request->input('type', 'deposit');
        $status = $request->input('status', 'confirmed');

        if ($amount <= 0) {
            return redirect()->back()->with('error', 'Amount must be greater than 0');
        }

        try {
            $attachmentId = null;
            if ($request->hasFile('attachment_id')) {
                $media = $this->uploadFile($request, 'attachment_id', 'image', 0);
                $attachmentId = $media->id;
            }

            $balanceBefore = (float) $row->credit_balance;

            // Status অনুযায়ী balance change
            $balanceAfter = $this->calculateBalance($balanceBefore, $type, $status, $amount);

            // Balance update
            $row->credit_balance = $balanceAfter;
            $row->save();

            Transaction::create([
                'user_id'          => $user_id,
                'type'             => $type,
                'amount'           => $amount,
                'status'           => $status,
                'reference'        => $request->reference,
                'transaction_type' => $request->transaction_type,
                'deposit_date'     => $request->deposit_date ?? date('Y-m-d'),
                'remarks'          => $request->remarks,
                'attachment_id'    => $attachmentId,
                'create_user'      => auth()->id(),
                'meta'             => json_encode([
                    'balance_before' => $balanceBefore,
                    'balance_after'  => $balanceAfter,
                    'payment_method' => $request->transaction_type,
                ]),
            ]);

        } catch (\Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->back()->with('success', __(':amount transaction added successfully', ['amount' => number_format($amount, 2).' BDT']));
    }

    /**
     * Status + Type অনুযায়ী balance calculate করুন
     */
    private function calculateBalance(float $before, string $type, string $status, float $amount): float
    {
        // Pending বা Void হলে balance change হবে না
        if (in_array($status, ['pending', 'void', 'voided'])) {
            return $before;
        }

        // Confirmed/Active statuses এ balance change হবে
        $increaseTypes = ['deposit', 'credit', 'topup'];   // + যোগ
        $decreaseTypes = ['withdraw', 'debit', 'payment']; // - বিয়োগ

        if (in_array($type, $increaseTypes)) {
            return $before + $amount;
        }

        if (in_array($type, $decreaseTypes)) {
            return $before - $amount;
        }

        // Refund status হলে + যোগ (client কে ফেরত)
        if ($status === 'refund' || $status === 'refunded') {
            return $before + $amount;
        }

        return $before;
    }

    public function report(){
        $query = DepositPayment::query();

        $query->where('object_model','wallet_deposit')->orderBy('id','desc');
        if($user_id = request()->query('user_id'))
        {
            $query->where('object_id',$user_id);
        }

        $data = [
            'rows'=>$query->paginate(20),
            'page_title'=>__("Credit purchase report"),
            'breadcrumbs'=>[
                [
                    'url'=>route('user.admin.index'),
                    'name'=>__("Users"),
                ],
                [
                    'url'=>'#',
                    'name'=>__('Credit purchase report'),
                ],
            ]
        ];
        return view("User::admin.wallet.report",$data);
    }

    public function reportBulkEdit(Request $request){
        $ids = $request->input('ids');
        $action = $request->input('action');
        if (empty($ids))
            return redirect()->back()->with('error', __('Select at lease 1 item!'));
        if (empty($action))
            return redirect()->back()->with('error', __('Select an Action!'));
        if ($action == 'delete') {
//            foreach ($ids as $id) {
//                if($id == Auth::id()) continue;
//                $query = User::where("id", $id)->first();
//                if(!empty($query)){
//                    $query->email.='_d';
//                    $query->save();
//                    $query->delete();
//                }
//            }
        } else {
            foreach ($ids as $id) {
                switch ($action){
                    case "completed":
                        $payment = DepositPayment::find($id);
                        if($payment->payment_gateway == 'offline_payment' and $payment->status == 'processing'){
                            $payment->markAsCompleted();
                            //$payment->sendUpdatedPurchaseEmail();
                        }
                        event(new UpdateCreditPurchase(Auth::user(), $payment));

                        break;
                }
            }
        }
        return redirect()->back()->with('success', __('Updated successfully!'));
    }

    public function updateRemarks(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('transaction_edit_remarks')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate(['remarks' => 'required|string|max:500']);

        $transaction = Transaction::findOrFail($id);
        $transaction->update(['remarks' => $request->remarks]);

        return response()->json(['success' => true, 'message' => 'Remarks updated successfully']);
    }

    public function updateAmount(Request $request, $id)
    {
        if (!auth()->user()->hasPermission('transaction_edit_amount')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate(['amount' => 'required|numeric|min:0']);

        $transaction = Transaction::findOrFail($id);
        $transaction->update(['amount' => $request->amount]);

        return response()->json(['success' => true, 'message' => 'Amount updated successfully']);
    }

    public function deleteTransaction($id)
    {
        if (!auth()->user()->hasPermission('transaction_delete')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {
            DB::beginTransaction();

            $transaction = Transaction::findOrFail($id);
            $user        = $transaction->user ?? User::find($transaction->user_id);

            // ✅ Confirmed transaction হলে credit_balance adjust করো
            if ($transaction->status === 'confirmed' && $user) {
                $isDeposit  = in_array($transaction->type, ['deposit', 'credit', 'topup', 'refund']);
                $isWithdraw = in_array($transaction->type, ['withdraw', 'debit', 'payment']);

                if ($isDeposit) {
                    // Deposit ছিল, তাই balance থেকে কমাও
                    $user->decrement('credit_balance', $transaction->amount);
                } elseif ($isWithdraw) {
                    // Withdraw ছিল, তাই balance-এ ফেরত যোগ করো
                    $user->increment('credit_balance', $transaction->amount);
                }
            }

            // ✅ Attachment থাকলে delete করো
            if ($transaction->attachment_id) {
                $mediaFile = \Modules\Media\Models\MediaFile::find($transaction->attachment_id);
                if ($mediaFile) {
                    \Illuminate\Support\Facades\Storage::disk($mediaFile->driver ?? 'uploads')
                        ->delete($mediaFile->file_path);
                    $mediaFile->delete();
                }
            }

            $transaction->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully' .
                    ($transaction->status === 'confirmed' && $user
                        ? ' and credit balance updated.'
                        : '.')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('❌ Delete Transaction Error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
