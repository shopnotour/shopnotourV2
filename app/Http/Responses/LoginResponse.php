<?php
//
//namespace App\Http\Responses;
//
//use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
//
//class LoginResponse implements LoginResponseContract
//{
//    public function toResponse($request)
//    {
//        $user = auth()->user();
//
//        if ($user->hasPermission('dashboard_access')) {
//            return redirect('/admin');
//        }
//
//        return redirect('/');
//    }
//}


namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = auth()->user();
        $redirect = $user->hasPermission('dashboard_access') ? '/admin' : '/';

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Login successful!',
                'redirect' => $redirect,
            ], 200);
        }

        return redirect($redirect);
    }
}
