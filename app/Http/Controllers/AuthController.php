<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Actions\LoginAction;
use App\Http\Actions\RegisterAction;
use Illuminate\Validation\Validator;

class AuthController extends Controller
{
    public function login(Request $request, LoginAction $action)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        $auth = $action->execute($request);

        return $this->okResponse('Login successful', $auth);
    }

    public function register(Request $request, RegisterAction $action)
    {
        $this->validate($request, [
            'firstName' => 'required|min:3|max:25',
            'firstName' => 'required|min:3|max:25',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        $user = $action->execute($request);

        return $this->createdResponse('User created successfully', $user);
    }

    public function checkEmail(Request $request)
    {
        $validator = validator(['email' => $request->email], ['email' => 'required|email|unique:users,email']);

        if ($validator->fails()) {
            return $this->badRequestResponse('Email address exists or is invalid');
        }

        return $this->okResponse('Email is valid');
    }
}
