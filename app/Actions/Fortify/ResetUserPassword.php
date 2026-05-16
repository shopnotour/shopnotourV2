<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  mixed  $user
     * @param  array  $input
     * @return void
     */
    public function reset($user, array $input)
    {
        Validator::make($input, [
            'password' => ['required', 'min:4', 'confirmed'],
            // 'password' => $this->passwordRules(), // hard rules বন্ধ
        ], [
            'password.required'  => 'নতুন পাসওয়ার্ড দিন।',
            'password.min'       => 'পাসওয়ার্ড কমপক্ষে ৪ অক্ষরের হতে হবে।',
            'password.confirmed' => 'পাসওয়ার্ড দুটো মিলছে না।',
        ])->validate();

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save();
    }
}
