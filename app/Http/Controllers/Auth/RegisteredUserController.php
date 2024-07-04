<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255',"unique:users,username,except,id"],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'max:255'],
            'password' => 'required|confirmed|min:8',
        ]);

        $user = User::query()->create([
            'username' => $request->get('username'),
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'phone' => $request->get('phone'),
            'password' => $request->get('password'),
        ]);

        return $user;
    }

    public function storeApi(Request $request)
    {
        $this->store($request);

        return $this->loginApi($request);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|min:8',
        ]);

        $login = $request->login;
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (auth()->attempt([$fieldType => $login, 'password' => $request->password])) {
            /**
             * @var User $user
             */

            $user = auth()->user();
            $user->access_token = $user->createToken('name')->plainTextToken;
            return $user;
        }

        return responder()->error(401, 'The provided credentials do not match our records.');
    }
}
