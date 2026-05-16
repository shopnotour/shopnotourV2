<?php


namespace Modules\User\Controllers;


use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\Booking\Models\Payment;
use Modules\FrontendController;
use Modules\Media\Traits\HasUpload;
use Modules\User\Events\RequestCreditPurchase;
use Modules\User\Models\Wallet\DepositPayment;
use Modules\User\Models\Wallet\Transaction;

class WalletController extends FrontendController
{
    use HasUpload;

    public function wallet()
    {
//        if (setting_item('wallet_module_disable')) {
//            return redirect(route("user.profile.index"));
//        }
        $row = auth()->user();
        $data = [
            'row' => $row,
            'page_title' => __("Wallet"),
            'breadcrumbs' => [
                [
                    'name' => __('Wallet'),
                    'class' => 'active'
                ]
            ],
            'transactions' => $row->transactions()->with(['payment', 'author'])->orderBy('id', 'desc')->paginate(15)
        ];
        return view('User::frontend.wallet.index', $data);
    }

    public function buy()
    {
        if (setting_item('wallet_module_disable')) {
            return redirect(route("user.profile.index"));
        }
        $row = auth()->user();
        $data = [
            'row' => $row,
            'page_title' => __("Buy credits"),
            'breadcrumbs' => [
                [
                    'name' => __('Wallet'),
                    'url' => route('user.wallet')
                ],
                [
                    'name' => __('Buy credits'),
                    'class' => 'active'
                ],
            ],
            'wallet_deposit_lists' => setting_item_array('wallet_deposit_lists', []),
            'gateways' => get_available_gateways()
        ];

        return view('User::frontend.wallet.buy', $data);
    }

    public function buyProcess(Request $request)
    {
        if (setting_item('wallet_module_disable')) {
            return redirect(route("user.profile.index"));
        }
        $row = auth()->user();
        $rules = [];
        $message = [];
        if (setting_item('wallet_deposit_type') == 'list') {
            $rules['deposit_option'] = 'required';
        } else {
            $rules['deposit_amount'] = 'required';
        }

        $payment_gateway = $request->input('payment_gateway');
        $gateways = get_payment_gateways();
        if (empty($payment_gateway)) {
            return redirect()->back()->with("error", __("Please select payment gateway"));
        }
        if (empty($payment_gateway) or empty($gateways[$payment_gateway]) or !class_exists($gateways[$payment_gateway])) {
            return redirect()->back()->with("error", __("Payment gateway not found"));
        }
        $gatewayObj = new $gateways[$payment_gateway]($payment_gateway);
        if (!$gatewayObj->isAvailable()) {
            return redirect()->back()->with("error", __("Payment gateway is not available"));
        }
        if ($gRules = $gatewayObj->getValidationRules()) {
            $rules = array_merge($rules, $gRules);
        }
        if ($gMessages = $gatewayObj->getValidationMessages()) {
            $message = array_merge($message, $gMessages);
        }
        $rules['payment_gateway'] = 'required';
        $rules['term_conditions'] = 'required';

        $validator = Validator::make($request->all(), $rules, $message);
        if ($validator->fails()) {
            if (is_array($validator->errors()->messages())) {
                $msg = '';
                foreach ($validator->errors()->messages() as $oneMessage) {
                    $msg .= implode('<br/>', $oneMessage) . '<br/>';
                }
                return redirect()->back()->with('error', $msg);
            }
            return redirect()->back()->with('error', $validator->errors());
        }

        $deposit_option = [];

        if (setting_item('wallet_deposit_type') == 'list') {
            $wallet_deposit_lists = setting_item_array('wallet_deposit_lists', []);
            $deposit_option = $request->input('deposit_option');
            if (empty($wallet_deposit_lists[$deposit_option])) {
                return redirect()->back()->with("error", __("Deposit option is not valid"));
            }
            if (empty($wallet_deposit_lists[$deposit_option]['amount'])) {
                return redirect()->back()->with("error", __("Deposit option amount is not valid"));
            }
            if (empty($wallet_deposit_lists[$deposit_option]['credit'])) {
                return redirect()->back()->with("error", __("Deposit option credit is not valid"));
            }
            $deposit_amount = $wallet_deposit_lists[$deposit_option]['amount'];
            $deposit_credit = $wallet_deposit_lists[$deposit_option]['credit'];
            $deposit_option = $wallet_deposit_lists[$deposit_option];
        } else {
            $deposit_amount = $request->input('deposit_amount');
            $deposit_credit = $deposit_amount * setting_item('wallet_deposit_rate', 1);
            if ($deposit_amount < 0) {
                return redirect()->back()->with("error", __("Deposit option credit is not valid"));
            }
        }

        $payment = new DepositPayment();
        $payment->object_model = 'wallet_deposit';
        $payment->object_id = $row->id;
        $payment->status = 'draft';
        $payment->payment_gateway = $payment_gateway;
        $payment->amount = $deposit_amount;

        $payment->save();

        $payment->addMeta('credit', $deposit_credit);
        $payment->addMeta('deposit_option', $deposit_option);

        $res = $gatewayObj->processNormal($payment);

        $success = $res[0] ?? null;
        $message = $res[1] ?? null;
        $redirect_url = $res[2] ?? null;

        if ($success) {
            $transaction = $row->draftDeposit($deposit_credit, $payment->id);
            $payment->wallet_transaction_id = $transaction->id;
            $payment->save();
            if (empty($redirect_url) and $payment->status == 'completed') {
                // Send Email
                //$payment->sendNewPurchaseEmail();
            }
            event(new RequestCreditPurchase($row, $payment));
        }

        if ($success and $payment->status == 'completed') $redirect_url = route('user.wallet');
        if ($redirect_url) {
            return redirect()->to($redirect_url)->with($success ? "success" : "error", $message);
        }
        return redirect()->back()->with($success ? "success" : "error", $message);
    }

    public function creditTransaction()
    {
        $query = Transaction::query();
        $credit_list = $query->where('user_id', auth()->id());

        $user = User::findOrFail(auth()->id());

        return view("User::frontend.wallet.credit_transactions", [
            'rows'       => $credit_list->get(), // paginate(15) সরিয়ে get() — DataTables pagination handle করে
            'users'      => $user,
            'page_title' => __("Transactions"),
            'breadcrumbs' => [
                [
                    'url'  => route('user.admin.index'),
                    'name' => __("Users"),
                ],
                [
                    'url'  => '#',
                    'name' => __('Credit transaction list'),
                ],
            ]
        ]);
    }

//    public function storecredit(Request $request)
//    {
//        $user = User::find(Auth::id());
//        if (!$user) {
//            abort(404);
//        }
//
//        $amount = $request->input('credit_amount', 0);
//
//        if ($amount) {
//            try {
//                $attachmentId = null;
//
//                if ($request->hasFile('attachment_id')) {
//                    $media = $this->uploadFile($request, 'attachment_id', 'image', 0);
//                    $attachmentId = $media->id;
//                }
//
//                Transaction::create([
//                    'user_id'          => auth()->id(),
//                    'type'             => 'deposit',
//                    'amount'           => $amount,
//                    'status'           => 'pending',
//                    'reference'        => $request->reference,
//                    'transaction_type' => $request->transaction_type,
//                    'deposit_date'     => $request->deposit_date,
//                    'remarks'          => $request->remarks,
//                    'attachment_id'    => $attachmentId,
//                    'create_user'      => auth()->id(),
//                    'update_user'      => '',
//                ]);
//
//            } catch (\Exception $exception) {
//                return redirect()->back()->with("error", $exception->getMessage());
//            }
//
//            return redirect()->back()->with("success", __(":amount credit added", ['amount' => $amount]));
//        }
//    }


    public function storecredit(Request $request)
    {
        $user = User::find(Auth::id());
        if (!$user) {
            abort(404);
        }

        $type   = $request->input('type', 'deposit'); // deposit | withdraw
        $amount = $request->input('amount', 0);

        // ✅ Basic validation
        if (!$amount || $amount <= 0) {
            return back()->with('error', 'Invalid amount');
        }

        try {

            $attachmentId = null;

            // =======================
            // 🔴 WITHDRAW
            // =======================
            if ($type === 'withdraw') {

                // ❗ balance check
                if ($amount > $user->credit_balance) {
                    return back()->with('error', 'Insufficient balance');
                }

                Transaction::create([
                    'user_id'          => $user->id,
                    'type'             => 'withdraw',
                    'amount'           => $amount,
                    'status'           => 'pending',
                    'deposit_date'     => now(),
                    'transaction_type' => $request->withdraw_method, // ✅ method সরাসরি যাবে
                    'meta'             => json_encode([
                        'method'    => $request->withdraw_method,
                        'bank_name' => $request->withdraw_bank_name,
                        'account'   => $request->account_number,
                        'note'      => $request->note,
                    ]),
//                    'create_user'      => $user->id,
                ]);

                return back()->with('success', 'Withdraw request submitted');
            }

            // =======================
            // 🟢 DEPOSIT
            // =======================
            else {

                if ($request->hasFile('attachment_id')) {
                    $media = $this->uploadFile($request, 'attachment_id', 'image', 0);
                    $attachmentId = $media->id;
                }

                Transaction::create([
                    'user_id'          => $user->id,
                    'type'             => 'deposit',
                    'amount'           => $amount,
                    'status'           => 'pending',
                    'reference'        => $request->reference,
                    // deposit bank হলে bank_name যাবে, নাহলে transaction_type যাবে
                    'transaction_type' => $request->transaction_type === 'bank'
                        ? ($request->bank_name ?: 'bank')
                        : $request->transaction_type,
                    'deposit_date' => now(),
                    'remarks'          => $request->remarks,
                    'attachment_id'    => $attachmentId,
//                    'create_user'      => $user->id,
                ]);

                return back()->with('success', "$amount deposit request submitted");
            }

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
    public function addcredit(Request $request)
    {
        $user=Auth::user();
//        return $user;
        return view("User::frontend.wallet.credit_add",[
                'users'=>$user
        ]

        );

    }
}

