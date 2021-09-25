<?php

namespace App\Http\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginAction
{
    public function execute(Request $request): array
    {
        if (!$token = Auth::attempt($request->input())) {
            abort(400, 'Invalid credentials');
        }

        return $this->formatToken($token);
    }

    private function formatToken(string $token): array
    {
        return [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ];
    }
}
