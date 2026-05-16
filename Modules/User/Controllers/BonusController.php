<?php

namespace Modules\User\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\FrontendController;
use Modules\User\Models\Wallet\Transaction;

class BonusController extends FrontendController
{
    public function index()
    {
        $user = Auth::user();

        $data = [
            'row'        => $user,
            'page_title' => __("Bonus & Points"),
            'breadcrumbs' => [
                [
                    'name'  => __('Bonus & Points'),
                    'class' => 'active'
                ]
            ],
            'transactions' => Transaction::where('user_id', $user->id)
                ->where('transaction_type', 'bonus')
                ->orderBy('id', 'desc')
                ->paginate(10),
            'bonus_enabled' => setting_item('bonus_enabled'),
            'point_enabled' => setting_item('point_enabled'),
        ];

        return view('User::frontend.bonus.index', $data);
    }

    public function applyCode(Request $request)
    {
        $request->validate([
            'bonus_code' => 'required|string',
        ]);

        $user      = Auth::user();
        $inputCode = trim($request->input('bonus_code'));
        $savedCode = setting_item('bonus_code');

        // Bonus enabled check
        if (!setting_item('bonus_enabled')) {
            return back()->with('error', __('Bonus system is not enabled.'));
        }

        // Code match check
        if (empty($savedCode) || strtoupper($inputCode) !== strtoupper($savedCode)) {
            return back()->with('error', __('Invalid bonus code.'));
        }

        // Role check
        $bonusRoles = setting_item_array('bonus_roles');
        if (!empty($bonusRoles) && !in_array($user->role_id, $bonusRoles)) {
            return back()->with('error', __('You are not eligible for this bonus.'));
        }

        // Already applied check
        $alreadyApplied = Transaction::where('user_id', $user->id)
            ->where('transaction_type', 'bonus')
            ->where('type', 'bonus_credit')
            ->where('reference', 'bonus_code:' . strtoupper($savedCode))
            ->exists();

        if ($alreadyApplied) {
            return back()->with('error', __('You have already applied this bonus code.'));
        }

        $bonusAmount = (float) setting_item('bonus_amount', 0);

        if ($bonusAmount <= 0) {
            return back()->with('error', __('Bonus amount is not configured.'));
        }

        // Credit bonus to user
        $user->bonus_balance = ($user->bonus_balance ?? 0) + $bonusAmount;
        $user->save();

        // Transaction record
        Transaction::create([
            'user_id'          => $user->id,
            'type'             => 'bonus_credit',
            'amount'           => $bonusAmount,
            'status'           => 'confirmed',
            'transaction_type' => 'bonus',
            'reference'        => 'bonus_code:' . strtoupper($savedCode),
            'remarks'          => __('Bonus credited via code: ') . strtoupper($savedCode),
            'create_user'      => $user->id,
        ]);

        return back()->with('success', __(':amount bonus has been added to your account.', [
            'amount' => format_money($bonusAmount)
        ]));
    }
}
