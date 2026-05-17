<?php
namespace Modules\User\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\AdminController;
use Modules\User\Events\VendorApproved;
use Modules\User\Models\Role;
use Modules\User\Models\User;
use Modules\Vendor\Models\VendorRequest;
use Modules\User\Exports\UserExport;

class UserController extends AdminController
{
    public function __construct()
    {
        $this->setActiveMenu(route('user.admin.index'));
    }

//    public function index(Request $request)
//    {
//        $this->checkPermission('user_view');
//        $username = $request->query('s');
//        $listUser = User::query()->orderBy('id','desc');
//        if (!empty($username)) {
//             $listUser->where(function($query) use($username){
//                 $query->where('first_name', 'LIKE', '%' . $username . '%');
//                 $query->orWhere('business_name', 'LIKE', '%' . $username . '%');
//                 $query->orWhere('id',  $username);
//                 $query->orWhere('phone',  $username);
//                 $query->orWhere('email', 'LIKE', '%' . $username . '%');
//                 $query->orWhere('last_name', 'LIKE', '%' . $username . '%');
//                 $query->orWhere(DB::raw("CONCAT(first_name,' ',last_name)"), 'LIKE', '%' . $username . '%');
//             });
//        }
//        if($request->query('role')){
//            $listUser->role($request->query('role'));
//        }
//        //$listUser->with(['wallet']);
//        $data = [
//            'rows' => $listUser->paginate(20),
//            'roles' => Role::all()
//        ];
//        return view('User::admin.index', $data);
//    }


    public function index(Request $request)
    {
        $this->checkPermission('user_view');

        // ✅ ajax() এর বদলে এটা use করুন
        if ($request->has('draw')) {

            $username = $request->input('search.value');
            $role     = $request->query('role');

            $query = User::query();

            if (!empty($username)) {
                $query->where(function ($q) use ($username) {
                    $q->where('first_name', 'LIKE', '%' . $username . '%')
                        ->orWhere('last_name',     'LIKE', '%' . $username . '%')
                        ->orWhere('business_name', 'LIKE', '%' . $username . '%')
                        ->orWhere('email',         'LIKE', '%' . $username . '%')
                        ->orWhere('phone',         $username)
                        ->orWhereRaw("CONCAT(first_name,' ',last_name) LIKE ?", ['%' . $username . '%']);
                    if (is_numeric($username)) {
                        $q->orWhere('id', $username);
                    }
                });
            }

            if (!empty($role)) {
                $query->role($role);
            }

            $totalRecords    = User::count();
            $filteredRecords = $query->count();
            $start           = (int) $request->query('start', 0);
            $length          = (int) $request->query('length', 20);

            $users = $query->orderBy('id', 'desc')
                ->skip($start)
                ->take($length)
                ->get();

            $data = $users->map(function ($row) {
                $verified = $row->email_verified_at
                    ? ' <i class="fa fa-check-circle text-success"></i>'
                    : ' <i class="fa fa-info-circle text-warning"></i>';

                $refHtml = $row->reference_id
                    ? '<span class="badge badge-info">' . $row->reference_id . '</span>'
                    : '<span class="badge badge-secondary">Not Set</span>';

                return [
                    'id'         => $row->id,
                    'name'       => '<a href="' . route('user.admin.detail', ['id' => $row->id]) . '">' . e($row->getDisplayName()) . '</a>',
                    'email'      => e($row->email) . $verified,
                    'balance'    => $row->balance,
                    'phone'      => e($row->phone ?? ''),
                    'role'       => $row->role->name ?? '',
                    'created_at' => display_date($row->created_at),
                    'reference'  => $refHtml,
                    'actions'    => $this->buildActionDropdown($row),
                ];
            });

            return response()->json([
                'draw'            => intval($request->query('draw')),
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data'            => $data,
            ]);
        }

        // Normal page load
        return view('User::admin.index', [
            'roles' => Role::all(),
            'rows'  => collect(),
        ]);
    }

// Action dropdown আলাদা method এ রাখলে index পরিষ্কার থাকে
    private function buildActionDropdown($row): string
    {
        $editUrl     = route('user.admin.detail',          ['id' => $row->id]);
        $passUrl     = route('user.admin.password',        ['id' => $row->id]);
        $creditUrl   = route('user.admin.wallet.addCredit',['id' => $row->id]);
        $creditList  = route('user.admin.wallet.list',     ['id' => $row->id]);
        $verifyUrl   = route('user.admin.verifyEmail', $row);

        $verifyBtn = $row->hasVerifiedEmail()
            ? '<a class="dropdown-item" href="#"><i class="fa fa-check"></i> ' . __('Email verified') . '</a>'
            : '<a class="dropdown-item" href="' . $verifyUrl . '"><i class="fa fa-edit"></i> ' . __('Verify email') . '</a>';

        return '
        <div class="dropdown">
            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                <i class="fa fa-th"></i>
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="' . $editUrl . '"><i class="fa fa-edit"></i> ' . __('Edit') . '</a>
                ' . $verifyBtn . '
                <a class="dropdown-item" href="' . $passUrl . '"><i class="fa fa-lock"></i> ' . __('Change Password') . '</a>
                <a class="dropdown-item" href="' . $creditUrl . '"><i class="fa fa-plus"></i> ' . __('Add Credit') . '</a>
                <a class="dropdown-item" href="' . $creditList . '"><i class="fa fa-list"></i> ' . __('Credit List') . '</a>
                <a href="#" class="dropdown-item set-reference-btn"
                   data-user-id="' . $row->id . '"
                   data-user-name="' . e($row->getDisplayName()) . '"
                   data-toggle="modal" data-target="#referenceModal">
                    <i class="fa fa-user"></i> ' . __('Set Reference') . '
                </a>
            </div>
        </div>';
    }
    public function create(Request $request)
    {

        $row = new \Modules\User\Models\User();
        $data = [
            'row' => $row,
            'roles' => Role::all(),
            'breadcrumbs'=>[
                [
                    'name'=>__("Users"),
                    'url'=>route('user.admin.index')
                ]
            ]
        ];
        return view('User::admin.detail', $data);
    }

    public function edit(Request $request, $id)
    {
        $row = User::find($id);
        if (empty($row)) {
            return redirect(route('user.admin.index'));
        }
        if ($row->id != Auth::user()->id and !Auth::user()->hasPermission('user_update')) {
            abort(403);
        }
        $data = [
            'row'   => $row,
            'roles' => Role::all(),
            'breadcrumbs'=>[
                [
                    'name'=>__("Users"),
                    'url'=>route('user.admin.index')
                ],
                [
                    'name'=>__("Edit User: #:id",['id'=>$row->id]),
                    'class' => 'active'
                ],
            ]
        ];
        return view('User::admin.detail', $data);
    }

    public function password(Request $request,$id){

        $row = User::find($id);
        $data  = [
            'row'=>$row,
            'currentUser'=>Auth::user()
        ];
        if (empty($row)) {
            return redirect(route('user.admin.index'));
        }
        if ($row->id != Auth::user()->id and !Auth::user()->hasPermission('user_update')) {
            abort(403);
        }
        return view('User::admin.password',$data);
    }

    public function changepass(Request $request, $id)
    {
        if(is_demo_mode()){
            return redirect()->back()->with("error", __("DEMO MODE: You can not change password!"));
        }
        $rules = [];
        $urow = User::find($id);
        if ($urow->id != Auth::user()->id and !Auth::user()->hasPermission('user_update')) {
            abort(403);
        }
        $request->validate([
            'password'              => 'required|min:6|max:255|confirmed',
        ]);
        $password = $request->input('password');
        if ($urow->id != Auth::user()->id and !Auth::user()->hasPermission('user_update')) {
            if ($password) {
                if ($urow->id != Auth::user()->id) {
                    $rules['old_password'] = 'required';
                }
                $rules['password'] = 'required|string|min:6|confirmed';
            }
            $this->validate($request, $rules);
            if ($password) {
                if (!(Hash::check($request->input('old_password'), $urow->password))) {
                    // The Old passwords matches
                    return redirect()->back()->with("error", __("Your current password does not matches with the password you provided. Please try again."));
                }
            }
        }
        $urow->password = bcrypt($password);
        $urow->setRememberToken(Str::random(60));
        if ($urow->save()) {

            if ($request->input('role_id') and $role = Role::findById($request->input('role_id'))) {
                $urow->assignRole($role);
            }
            return redirect()->back()->with('success', __('Password updated!'));
        }
    }

    public function store(Request $request, $id)
    {
        if(is_demo_mode()){
            return back()->with('danger',  __('DEMO Mode: You can not do this') );
        }

        if($id and $id>0){
            $this->checkPermission('user_update');
            $row = User::find($id);
            if(empty($row)){
                abort(404);
            }
            if ($row->id != Auth::user()->id and !Auth::user()->hasPermission('user_update')) {
                abort(403);
            }

        }else{
            $this->checkPermission('user_create');
            $row = new User();
        }

        $rules = [
            'first_name'              => 'required|max:255',
            'last_name'              => 'required|max:255',
            'business_name'              => 'required|max:255',
//            'status'              => 'nullable|required|max:50',
//            'role_id'              => 'nullable|required|max:11',
            'email'              =>[
                'required',
                'email',
                'max:255',
                $id > 0 ? Rule::unique('users')->ignore($row->id) : Rule::unique('users')
            ],
            'user_name'=> [
                'required',
                'max:255',
                'min:4',
                'string',
                'alpha_dash',
                $id > 0 ? Rule::unique('users')->ignore($row->id) : Rule::unique('users')
            ],
        ];

        $request->validate($rules,[
            'business_name.required'=>__("Display name is a required field")
        ]);

        $data = [
            'first_name'=>$request->input('first_name'),
            'last_name'=>$request->input('last_name'),
            'user_name'=>$request->input('user_name'),
            'phone'=>$request->input('phone'),
            'iata_number'=>$request->input('iata_number'),
            'civil_aviation_number'=>$request->input('civil_aviation_number'),
            'trade_license_number'=>$request->input('trade_license_number'),
            'birthday'=>$request->input('birthday') ? date("Y-m-d", strtotime($request->input('birthday'))) : null,
            'bio'=>$request->input('bio'),
            'iata_file_id'=>$request->input('iata_file_id'),
            'civil_aviation_file_id'=>$request->input('civil_aviation_file_id'),
            'trade_license_file_id'=>$request->input('trade_license_file_id'),
            'status'=>$request->input('status'),
            'avatar_id'=>$request->input('avatar_id'),
            'email'=>$request->input('email'),
            'business_name'=>$request->input('business_name'),
            'name'=>$request->input('name'),
            'address'=>$request->input('address'),
            'address2'=>$request->input('address2'),
            'country'=>$request->input('country'),
            'city'=>$request->input('city'),
            'state'=>$request->input('state'),
            'zip_code'=>$request->input('zip_code'),
            'vendor_commission_type'=>$request->input('vendor_commission_type'),
            'vendor_commission_amount'=>$request->input('vendor_commission_amount'),
        ];
        $row->role_id = $request->input('role_id');
        if($request->input('is_email_verified')){
            if(!$row->email_verified_at) $row->email_verified_at = date('Y-m-d H:i:s');
        }else{
            $row->email_verified_at = null;
        }

        $row->fillByAttr(array_keys($data),$data);

        //Block all service when user is block
        if($row->status == "blocked"){
            $services = get_bookable_services();
            if(!empty($services)){
                foreach ($services as $service){
                    $service::query()->where("create_user",$row->id)->update(['status' => "draft"]);
                }
            }
        }

        if ($row->save()) {
            return back()->with('success', ($id and $id>0) ? __('User updated'):__("User created"));
        }
    }

    public function getForSelect2(Request $request)
    {
        $pre_selected = $request->query('pre_selected');
        $selected = $request->query('selected');
        if ($pre_selected && $selected) {
            if (is_array($selected)) {
                $res = User::whereIn('id', $selected)->take(50)->get();
                $items = [];
                if(!empty($res)){
                    foreach ($res as $item){
                        $items[] = [
                            'id'=>$item->id,
                            'text'=>$item->getDisplayName() ? $item->getDisplayName() . ' (#' . $item->id . ')' : $item->email . ' (#' . $item->id . ')'
                        ];
                    }
                }
            } else {
                $items = [];
                $item = User::find($selected);
                if(!empty($item)){
                    $items[] = [
                        'text' => $item->getDisplayName() ? $item->getDisplayName() . ' (#' . $item->id . ')' : $item->email . ' (#' . $item->id . ')'
                    ];
                }
            }

            return [
                'results'=>$items
            ];
        }

        $q = $request->query('q');
        $query = User::select('*');
        if ($q) {
            $query->where(function ($query) use ($q) {
                $query->where('first_name', 'like', '%' . $q . '%')->orWhere('last_name', 'like', '%' . $q . '%')->orWhere('email', 'like', '%' . $q . '%')->orWhere('id', $q)->orWhere('phone', 'like', '%' . $q . '%');
            });
        }
        $res = $query->orderBy('id', 'desc')->orderBy('first_name', 'asc')->limit(100)->get();
        $data = [];
        if (!empty($res)) {
            if($request->query("user_type") == "vendor"){
                //for only vendor
                foreach ($res as $item) {
                    if($item->hasPermission("dashboard_vendor_access")){
                        $data[] = [
                            'id'   => $item->id,
                            'text' => $item->getDisplayName() ? $item->getDisplayName() . ' (#' . $item->id . ')' : $item->email . ' (#' . $item->id . ')',
                        ];
                    }
                }
            }else{
                //for all
                foreach ($res as $item) {
                    $data[] = [
                        'id'   => $item->id,
                        'text' => $item->getDisplayName() ? $item->getDisplayName() . ' (#' . $item->id . ')' : $item->email . ' (#' . $item->id . ')',
                    ];
                }
            }
        }
        return response()->json([
            'results' => $data
        ]);
    }

    public function bulkEdit(Request $request)
    {
        if(is_demo_mode()){
            return redirect()->back()->with("error","DEMO MODE: You are not allowed to do it");
        }
        $ids = $request->input('ids');
        $action = $request->input('action');
        if (empty($ids))
            return redirect()->back()->with('error', __('Select at least 1 item!'));
        if (empty($action))
            return redirect()->back()->with('error', __('Select an Action!'));
        if ($action == 'delete') {
            foreach ($ids as $id) {
                if($id == Auth::id()) continue;
                $query = User::where("id", $id)->first();
                if(!empty($query)){
                    $query->email.='_d_'.uniqid().rand(0,99999);
                    $query->save();
                    $query->delete();
                }
            }
        } else {
            foreach ($ids as $id) {
                User::where("id", $id)->update(['status' => $action]);
            }
        }
        return redirect()->back()->with('success', __('Updated successfully!'));
    }
    public function userUpgradeRequest(Request $request)
    {
        $this->checkPermission('user_view');
        $listUser = VendorRequest::query();
        $data = [
            'rows' => $listUser->whereHas('user')->with(['user','role','approvedBy'])->orderBy('id','desc')->paginate(20),
            'roles' => Role::all(),

        ];
        return view('User::admin.upgrade-user', $data);
    }
//    public function userUpgradeRequestApproved(Request $request)
//    {
//        $this->checkPermission('user_create');
//        $ids = $request->input('ids');
//        $action = $request->input('action');
//        if (empty($ids))
//            return redirect()->back()->with('error', __('Select at leas 1 item!'));
//        if (empty($action))
//            return redirect()->back()->with('error', __('Select an Action!'));
//
//        switch ($action){
//            case "delete":
//                foreach ($ids as $id) {
//                    $query = VendorRequest::find( $id);
//                    if(!empty($query)){
//                        $query->delete();
//                    }
//                }
//                return redirect()->back()->with('success', __('Deleted success!'));
//                break;
//            default:
//                foreach ($ids as $id) {
//                    $vendorRequest = VendorRequest::find( $id);
//                    if(!empty($vendorRequest)){
//                        $vendorRequest->update(['status' => $action,'approved_time'=>now(),'approved_by'=>Auth::id()]);
//                        $user = User::find($vendorRequest->user_id);
//                        if(!empty($user)){
//                            $user->assignRole($vendorRequest->role_request);
//                        }
//                        event(new VendorApproved($user,$vendorRequest));
//                    }
//                }
//                return redirect()->back()->with('success', __('Updated successfully!'));
//                break;
//        }
//    }
//    public function userUpgradeRequestApprovedId(Request $request, $id)
//    {
//        $this->checkPermission('user_create');
//        if (empty($id))
//            return redirect()->back()->with('error', __('Select at least 1 item!'));
//
//        $vendorRequest = VendorRequest::find( $id);
//        if(!empty($vendorRequest)){
//            $vendorRequest->update(['status' => 'approved','approved_time'=>now(),'approved_by'=>Auth::id()]);
//            $user = User::find($vendorRequest->user_id);
//            if(!empty($user)){
//                $user->assignRole($vendorRequest->role_request);
//            }
//
//            event(new VendorApproved($user,$vendorRequest));
//        }
//        return redirect()->back()->with('success', __('Updated successfully!'));
//    }

    public function userUpgradeRequestApproved(Request $request)
    {
        $this->checkPermission('user_create');
        $ids = $request->input('ids');
        $action = $request->input('action');

        if (empty($ids))
            return redirect()->back()->with('error', __('Select at leas 1 item!'));
        if (empty($action))
            return redirect()->back()->with('error', __('Select an Action!'));

        switch ($action) {
            case "delete":
                foreach ($ids as $id) {
                    $query = VendorRequest::find($id);
                    if (!empty($query)) {
                        $query->delete();
                    }
                }
                return redirect()->back()->with('success', __('Deleted success!'));
                break;

            default:
                foreach ($ids as $id) {

                    $vendorRequest = VendorRequest::find($id);

                    if (!empty($vendorRequest)) {

                        $oldStatus = $vendorRequest->status;

                        $vendorRequest->update([
                            'status'        => $action,
                            'approved_time' => now(),
                            'approved_by'   => Auth::id()
                        ]);

                        $user = User::find($vendorRequest->user_id);

                        if (!empty($user)) {

                            $user->assignRole($vendorRequest->role_request);

                            // Get bonus settings
                            $bonusEnabled = DB::table('core_settings')
                                ->where('name', 'bonus_enabled')
                                ->value('val');

                            $bonusAmount = DB::table('core_settings')
                                ->where('name', 'bonus_amount')
                                ->value('val');

                            if ((int)$bonusEnabled === 1 && $action === 'approved') {

                                $balanceBefore = (float)$user->bonus_balance;
                                $user->bonus_balance = $balanceBefore + (float)$bonusAmount;
                                $user->save();

                                // ✅ Transaction insert
                                DB::table('credit_transactions')->insert([
                                    'user_id'          => $user->id,
                                    'booking_id'       => null,
                                    'ref_id'           => null,
                                    'type'             => 'deposit',
                                    'amount'           => (float)$bonusAmount,
                                    'meta'             => json_encode([
                                        'balance_before' => $balanceBefore,
                                        'balance_after'  => $user->bonus_balance,
                                    ]),
                                    'status'           => 'confirmed',
                                    'create_user'      => Auth::id(),
                                    'update_user'      => Auth::id(),
                                    'reference'        => 'vendor_approval_bonus',
                                    'transaction_type' => 'bonus',
                                    'remarks'          => 'Vendor approval bonus',
                                    'created_at'       => now(),
                                    'updated_at'       => now(),
                                ]);
                            }
                        }

                        event(new VendorApproved($user, $vendorRequest));
                    }
                }

                return redirect()->back()->with('success', __('Updated successfully!'));
                break;
        }
    }

    public function userUpgradeRequestApprovedId(Request $request, $id)
    {
        $this->checkPermission('user_create');

        if (empty($id)) {
            return redirect()->back()->with('error', __('Select at least 1 item!'));
        }

        $vendorRequest = VendorRequest::find($id);

        if (!empty($vendorRequest)) {

            $oldStatus = $vendorRequest->status;

            $vendorRequest->update([
                'status'        => 'approved',
                'approved_time' => now(),
                'approved_by'   => Auth::id()
            ]);

            $user = User::find($vendorRequest->user_id);

            if (!empty($user)) {

                $user->assignRole($vendorRequest->role_request);

                // Get bonus settings
                $bonusEnabled = DB::table('core_settings')
                    ->where('name', 'bonus_enabled')
                    ->value('val');

                $bonusAmount = DB::table('core_settings')
                    ->where('name', 'bonus_amount')
                    ->value('val');

                if ((int)$bonusEnabled === 1) {

                    $balanceBefore = (float)$user->bonus_balance;
                    $user->bonus_balance = $balanceBefore + (float)$bonusAmount;
                    $user->save();

                    // ✅ Transaction insert
                    DB::table('credit_transactions')->insert([
                        'user_id'          => $user->id,
                        'booking_id'       => null,
                        'ref_id'           => null,
                        'type'             => 'deposit',
                        'amount'           => (float)$bonusAmount,
                        'meta'             => json_encode([
                            'balance_before' => $balanceBefore,
                            'balance_after'  => $user->bonus_balance,
                        ]),
                        'status'           => 'confirmed',
                        'create_user'      => Auth::id(),
                        'update_user'      => Auth::id(),
                        'reference'        => 'vendor_approval_bonus',
                        'transaction_type' => 'bonus',
                        'remarks'          => 'Vendor approval bonus',
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }

            event(new VendorApproved($user, $vendorRequest));
        }

        return redirect()->back()->with('success', __('Updated successfully!'));
    }

    public function export()
    {
        $this->checkPermission('user_view');
        return (new UserExport())->download('user-' . date('M-d-Y') . '.xlsx');
    }
    public function verifyEmail(Request $request,$id)
    {
        $user = User::find($id);
        if(!empty($user)){
            $user->email_verified_at = now();
            $user->save();
            return redirect()->back()->with('success', __('Verify email successfully!'));
        }else{
            return redirect()->back()->with('error', __('Verify email cancel!'));
        }
    }

    public function setReference(Request $request, $id)
    {
        if(is_demo_mode()){
            return response()->json([
                'status' => false,
                'message' => __('DEMO Mode: You can not do this')
            ]);
        }

        $user = User::find($id);
        if(empty($user)){
            return response()->json([
                'status' => false,
                'message' => __('User not found')
            ]);
        }

        $reference_id = $request->input('reference_id');

        // Optional: Prevent self-reference
        if($reference_id == $id){
            return response()->json([
                'status' => false,
                'message' => __('User cannot refer to themselves')
            ]);
        }

        // Optional: Check if reference user exists
        if($reference_id){
            $referenceUser = User::find($reference_id);
            if(!$referenceUser){
                return response()->json([
                    'status' => false,
                    'message' => __('Reference user not found')
                ]);
            }
        }

        $user->reference_id = $reference_id;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => __('Reference set successfully'),
            'reference_id' => $reference_id
        ]);
    }

    public function update(Request $request, $id)
    {
        $row = User::find($id);
        if (empty($row)) {
            return redirect(route('user.admin.index'));
        }
        if ($row->id != Auth::user()->id and !Auth::user()->hasPermission('user_update')) {
            abort(403);
        }
        $data = [
            'row'   => $row,
            'roles' => Role::all(),
            // 'breadcrumbs'=>[
            //     [
            //         'name'=>__("Users"),
            //         'url'=>route('user.admin.index')
            //     ],
            //     [
            //         'name'=>__("Edit User: #:id",['id'=>$row->id]),
            //         'class' => 'active'
            //     ],
            // ]
        ];
        return view('User::admin.approved_details', $data);
    }


}
