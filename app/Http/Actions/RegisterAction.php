<?php

namespace App\Http\Actions;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterAction
{
    public function execute(Request $request): User
    {
        return User::create([
            'uuid' => (string) Str::uuid(),
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
    }
}
