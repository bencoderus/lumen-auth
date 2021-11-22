<?php

namespace App\Http\Actions;

use App\Models\User;
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

        $user = User::where('email', $request->input('email'))->first();

        return $this->formatToken($token, $user);
    }

    private function formatToken(string $token, User $user): array
    {
        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60
        ];
    }
}
