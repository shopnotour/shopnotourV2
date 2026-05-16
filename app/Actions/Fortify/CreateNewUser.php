<?php
//
//namespace App\Actions\Fortify;
//
//use App\Models\User;
//use Illuminate\Support\Facades\Hash;
//use Illuminate\Support\Facades\Validator;
//use Illuminate\Validation\Rule;
//use Laravel\Fortify\Contracts\CreatesNewUsers;
//
//class CreateNewUser implements CreatesNewUsers
//{
//    use PasswordValidationRules;
//
//    /**
//     * Validate and create a newly registered user.
//     *
//     * @param  array  $input
//     * @return \App\Models\User
//     */
//    public function create(array $input)
//    {
//        Validator::make($input, [
//            'name' => ['required', 'string', 'max:255'],
//            'email' => [
//                'required',
//                'string',
//                'email',
//                'max:255',
//                Rule::unique(User::class),
//            ],
//            'password' => ['required', 'min:4', 'confirmed'],
////            'password' => $this->passwordRules(),
//        ])->validate();
//
//        return User::create([
//            'name' => $input['name'],
//            'email' => $input['email'],
//            'password' => Hash::make($input['password']),
//        ]);
//    }
//}


namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input)
    {

        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password' => ['required', 'min:4', 'confirmed'],
            'term' => ['accepted'],
        ])->validate();

        return User::create([
            'first_name' => trim($input['first_name']),
            'last_name' => trim($input['last_name'] ?? ''),
            'name' => trim(($input['first_name'] ?? '') . ' ' . ($input['last_name'] ?? '')),
            'email' => strtolower(trim($input['email'])),
            'password' => Hash::make($input['password']),
        ]);
    }
}
