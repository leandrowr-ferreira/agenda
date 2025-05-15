<?php

use App\Models\User;
use App\Models\UserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/', function () {
    if (Auth::check()) return view('singup/steps/one');

    return view('index');
});

Route::get('/auth/callback/google', function () {
    $googleUser = Socialite::driver('google')->user();

    if ($googleUser) {
        $provider = UserProvider::where('provider', 'google')
            ->where('provider_id', $googleUser->getId())
            ->first();

        if ($provider) {
            $user = $provider->user;
            $provider->update([
                'access_token' => $googleUser->token,
                'refresh_token' => $googleUser->refreshToken ?? $provider->refreshToken,
                'token_expires_at' => now()->addSeconds()
            ]);
        } else {
            $user = User::create([
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'email_verified_at' => now()
            ]);

            $user->providers()->create([
                'provider' => 'google',
                'provider_id' => $googleUser->getId(),
                'access_token' => $googleUser->token,
                'refresh_token' => $googleUser->refreshToken,
                'token_expires_at' => now()->addSeconds($googleUser->expiresIn),
            ]);
        }

        Auth::login($user);
    }

    return redirect('/');
});

Route::get('/auth/redirect/google', function () {
    return Socialite::driver('google')->scopes(['openid', 'profile', 'email', 'https://www.googleapis.com/auth/calendar'])
        ->with(['access_type' => 'offline', 'prompt' => 'consent'])
        ->redirect();
});

