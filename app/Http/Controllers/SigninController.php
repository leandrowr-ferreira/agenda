<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProvider;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SigninController extends Controller
{
    public function index() 
    {
        return view('signin/index');
    }

    public function auth($type, $provider)
    {
        switch ($provider) {
            case 'google': 
                return $this->google_provider($type);
                break;     
            default:
                return view('signin/index');
        }
    }

    private function google_provider($type)
    {
        if ($type == 'redirect') {
            return Socialite::driver('google')->scopes([
                'openid',
                'profile',
                'email',
                'https://www.googleapis.com/auth/calendar'
            ])->with(['access_type' => 'offline', 'prompt' => 'consent'])
                ->redirect();
        }

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
    }
}
