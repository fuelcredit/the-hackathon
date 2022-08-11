<?php

namespace App\Models\Validators;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

class UserValidator{

    public function validate(User $user, array $attributes): array
    {
        return Validator($attributes, [
            'firstName' => [Rule::when($user->exists, 'sometimes'), 'required', 'string', 'min:3'],
            'lastName' => [Rule::when($user->exists, 'sometimes'), 'required', 'string', 'min:3'],
            'mobileNumber' => [Rule::when($user->exists, 'sometimes'), 'required', 'string', 'min:11', 'max:15', Rule::unique('users', 'mobileNumber')->ignore($user->id)],
            'email' => ['nullable', 'string', 'email', 'min:5', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            // Password::min(8)->letters()->mixedCase()->numbers()->symbols()
            'password' => [Rule::when($user->exists, 'sometimes'), 'required', 'string', 'min:8',],
            'nin' => ['nullable', 'string', 'min:10', 'max:15', Rule::unique('users', 'nin')->ignore($user->id)],
            'bvn' => ['nullable','string', 'min:10', 'max:15', Rule::unique('users', 'bvn')->ignore($user->id)],
        ])->validate();
    }
}