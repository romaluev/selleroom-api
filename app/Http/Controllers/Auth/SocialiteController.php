<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Str;

class SocialiteController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider)
    {
        $socialiteUser = Socialite::driver($provider)->user();

        // Check if email exists in the response
        if (!isset($socialiteUser->email)) {
            return redirect()->route('login')->withErrors(['email' => 'No email provided by the provider.']);
        }

        $existingUser = User::where('email', $socialiteUser->email)->first();

        if ($existingUser) {
            Auth::login($existingUser);
        } else {
            // Check if the other properties exist
            $name = isset($socialiteUser->name) ? $socialiteUser->name : 'No Name';
            $username = isset($socialiteUser->nickname) ? $socialiteUser->nickname : 'No Username';
            $providerId = isset($socialiteUser->id) ? $socialiteUser->id : '';
            $providerToken = isset($socialiteUser->token) ? $socialiteUser->token : '';

            $user = User::create([
                'name'          => $name,
                'username'      => $username,
                'email'         => $socialiteUser->email,
                'password'      => Hash::make(Str::random(8)),
                'provider_name' => $provider,
                'provider_id'   => $providerId,
                'provider_token' => $providerToken
            ]);

            event(new Registered($user));

            Auth::login($user);
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }
}
