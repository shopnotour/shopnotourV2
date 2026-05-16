<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message'  => 'Account created successfully!',
                'redirect' => '/',
            ], 201);
        }

        return redirect('/');
    }
}
