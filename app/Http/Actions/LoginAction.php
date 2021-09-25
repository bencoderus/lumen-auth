<?php

namespace App\Http\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class LoginAction
{
    public function execute(Request $request): array
    {
        if (!$token = Auth::attempt($request->input())) {
            throw new HttpException(401, 'Invalid credentials');
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
