<?php

namespace Modules\User\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\AdminController;
use Modules\User\Models\Role;
use Modules\User\Models\User;
use Modules\User\Models\Wallet\Transaction;

class BonusController extends AdminController
{
    public function __construct()
    {
        $this->setActiveMenu(route('user.admin.index'));
    }

    // ── Manual Bonus Page ──
    public function index(Request $request)
    {
        $this->checkPermission('bonus_manage');

        $data = [
            'page_title'  => __('Manual Bonus'),
            'roles'       => Role::all(),
            'breadcrumbs' => [
//                [
//                    'name' => __('Users'),
//                    'url'  => route('user.admin.index'),
//                ],
                [
                    'name'  => __('Manual Bonus'),
                    'class' => 'active',
                ],
            ],
        ];

        return view('User::admin.bonus.index', $data);
    }

    // ── Give Bonus ──
    public function store(Request $request)
    {
        $this->checkPermission('bonus_give');

        $request->validate([
            'target_type' => 'required|in:all,role,specific',
            'bonus_type'  => 'required|in:bonus_balance,bonus_points',
            'amount'      => 'required|numeric|min:0.01',
            'remarks'     => 'nullable|string|max:255',
            'role_id'     => 'required_if:target_type,role',
            'user_ids'    => 'required_if:target_type,specific',
        ]);

        $targetType = $request->input('target_type');
        $bonusType  = $request->input('bonus_type');
        $amount     = (float) $request->input('amount');
        $remarks    = $request->input('remarks') ?? __('Manual bonus by admin');
        $adminId    = Auth::id();

        $query = User::query();

        if ($targetType === 'role') {
            $query->where('role_id', $request->input('role_id'));
        } elseif ($targetType === 'specific') {
            $userIds = $request->input('user_ids', []);
            if (!is_array($userIds)) {
                $userIds = array_filter(explode(',', $userIds));
            }
            $userIds = array_filter(array_map('intval', $userIds));
            if (empty($userIds)) {
                return back()->with('error', __('Please select at least one user.'));
            }
            $query->whereIn('id', $userIds);
        }

        $count = 0;

        $query->chunk(100, function ($users) use (
            $bonusType, $amount, $remarks, $adminId, &$count
        ) {
            foreach ($users as $user) {
                if ($bonusType === 'bonus_balance') {
                    $user->bonus_balance = ($user->bonus_balance ?? 0) + $amount;
                } else {
                    $user->bonus_points = ($user->bonus_points ?? 0) + (int) $amount;
                }
                $user->save();

                Transaction::create([
                    'user_id'          => $user->id,
                    'type'             => 'bonus_credit',
                    'amount'           => $amount,
                    'status'           => 'confirmed',
                    'transaction_type' => 'bonus',
                    'reference'        => 'admin_manual',
                    'reference_id'     => $adminId,
                    'remarks'          => $remarks,
                    'create_user'      => $adminId,
                ]);

                $count++;
            }
        });

        if ($count === 0) {
            return back()->with('error', __('No users found for selected target.'));
        }

        return back()->with('success', __('Bonus given to :count users successfully.', ['count' => $count]));
    }

    // ── Bonus Transactions List ──
    public function transactions(Request $request)
    {
        $this->checkPermission('bonus_transactions');

        $query = Transaction::with('author')
            ->where('transaction_type', 'bonus')
            ->orderBy('id', 'desc');

        if ($s = $request->query('s')) {
            $query->whereHas('author', function ($q) use ($s) {
                $q->where('first_name', 'like', "%$s%")
                    ->orWhere('last_name', 'like', "%$s%")
                    ->orWhere('email', 'like', "%$s%");
            });
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        $data = [
            'rows'        => $query->paginate(20),
            'page_title'  => __('Bonus Transactions'),
            'breadcrumbs' => [
//                [
//                    'name' => __('Users'),
//                    'url'  => route('user.admin.index'),
//                ],
                [
                    'name'  => __('Bonus Transactions'),
                    'class' => 'active',
                ],
            ],
        ];

        return view('User::admin.bonus.transactions', $data);
    }
}
