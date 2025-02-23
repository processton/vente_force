<?php

namespace Crater\Http\Controllers\V1\Admin\Auth;

use Crater\Http\Controllers\Controller;
use Crater\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Support\Facades\Cookie;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

class OAuthLoginController extends Controller
{

    public function index(Request $request)
    {
        $appKey = tenant('identity_force_app_key');
        $appUrl = tenant('identity_force_app_url');
        $request->session()->put('state', $state = Str::random(40));

        $request->session()->put(
            'code_verifier',
            $code_verifier = Str::random(128)
        );

        $codeChallenge = strtr(rtrim(
            base64_encode(hash('sha256', $code_verifier, true)),
            '='
        ), '+/', '-_');

        $query = http_build_query([
            'client_id' => $appKey,
            'redirect_uri' => url('/oauth/callback'),
            'response_type' => 'code',
            'scope' => '*',
            'state' => $state,
            'code_challenge' => $codeChallenge,
            'code_challenge_method' => 'S256',
            // 'prompt' => '', // "none", "consent", or "login"
        ]);

        return redirect()->away($appUrl . '?'. $query);
    }

    public function callback(Request $request){

        $appKey = tenant('identity_force_app_key');
        $appSecret = tenant('identity_force_app_secret');
        $appUrl = tenant('identity_force_app_url');

        $appUrl = str_replace('/oauth/authorize', '', $appUrl);

        $state = $request->session()->pull('state');

        $codeVerifier = $request->session()->pull('code_verifier');

        throw_unless(
            strlen($state) > 0 && $state === $request->state,
            InvalidArgumentException::class,
            'Invalid state value.'
        );

        $response = Http::asForm()->post($appUrl . '/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => $appKey,
            'client_secret' => $appSecret,
            'redirect_uri' => url('/oauth/callback'),
            'code_verifier' => $codeVerifier,
            'code' => $request->code,
        ]);


        $data = $response->json();
        $token = $data['access_token'];

        $cookie = Cookie::make('bearer_token', $token, 60 * 24, '/', null);

        return redirect('/collect-profile')
        ->withCookie($cookie);
    }

    public function collectProfile(Request $request)
    {
        $appUrl = tenant('identity_force_app_url');
        $token = request()->cookie('bearer_token');
        $appUrl = str_replace('/oauth/authorize', '', $appUrl);

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token
        ])->get($appUrl . '/api/user', );

        $user = $response->json();

        dd($user, $token);

        $user = \Crater\Models\User::where('email', $user['email'])->first();

        if(!$user){
            $user = \Crater\Models\User::create([
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]);
        }

        auth()->login($user);

        return redirect(RouteServiceProvider::HOME);
    }

    public function redirectToProcesston ()
    {
        return Socialite::driver('processton')->stateless()->redirect();
    }

    //Google callback
    public function handleProcesstonCallback()
    {

        $user = Socialite::driver('processton')->stateless()->user();

        dd($user);
        $this->_registerorLoginUser($user);
        return redirect()->route('home');
    }
}
