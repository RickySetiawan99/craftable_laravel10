<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class ProviderController extends Controller
{
    public function redirect($provider)
    {
        return Socialite::driver($provider)->redirect();
    }
    
    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();

            $cekUser = User::where([
                'email'     => $socialUser->getEmail(),
                'provider'  => $provider
            ])->first();

            if($cekUser){
                Auth::login($cekUser);
         
                return redirect('/dashboard');
            }else{
                if(User::where('email', $socialUser->getEmail())->exists()){
                    return redirect('/login')->withErrors(['email' => 'Email Sudah Digunakan']);
                }

                $user = User::updateOrCreate([
                    'provider_id'   => $socialUser->id,
                    'provider'      => $provider
                ], [
                    'name'              => $socialUser->name,
                    'email'             => $socialUser->email,
                    'provider'          => $provider,
                    'provider_id'       => $socialUser->getId(),
                    'provider_token'    => $socialUser->token,
                    'email_verified_at' => now(),
                ]);
    
                Auth::login($user);
             
                return redirect('/dashboard');
            }

        } catch (\Throwable $th) {
            return redirect('/login');
        }
    }
}
