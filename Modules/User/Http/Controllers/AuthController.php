<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Modules\User\Entities\User;
use Modules\User\Http\Requests\LoginRequest;
use Modules\User\Http\Requests\UpdateProfileRequest;
use Modules\User\Proxies\Entities\UserProvider;
use Modules\User\Proxies\Repositories\UserRepository;
use Modules\User\Transformers\UserResource;


class AuthController extends Controller
{
    /**
     * authentication via username/email and password
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authenticate(LoginRequest $request)
    {
        /* get user via username */
        $user = UserRepository::where(['username' => $request->username])->orWhere(['email' => $request->username])->first();

        if (! $user || !$user->status_id || ! Hash::check($request->password, $user->password)) {
            return response()->json( [ 'error' => __('Authorization information is missing or invalid.') ], 401 );
        }

        /* create user session */
        Auth::login($user);

        $token =  $user->createToken('global')->plainTextToken;

        $data = ['id' => $user->id, 'username' => $user->username, 'email' => $user->email, 'api_token' => $token];


        if(!empty($user->roles))
        {
            $data['roles'] = $user->roles->pluck('name')->toArray();
        }

        return response()->json(['data' => $data]);
    }

    /**
     * redirect user to provider website for authentication
     *
     * @param $provider
     * @return mixed
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * handle provider callback
     * @param $provider
     */
    public function handleProviderCallback($provider)
    {
        $providerData = Socialite::driver($provider)->stateless()->user();

        if(!empty($providerData['id']) && !empty($providerData['email']))
        {
            $user = null;

            /*check if provider already exists */
            $providerObj = UserProvider::where([
                'provider_id'   => $providerData['id'], 'provider_name' => $provider
            ])->first();

            if($providerObj)
            {
                $user = $provider->user;
            }
            else
            {
                /* check if */
                $user = UserRepository::where('email', $providerData['email'])->first();

                if(!$user)
                {
                    $username = (explode('@', $providerData['email'])[0]) . '-' . rand(100, 999);

                    /*create new user account */
                    $userData = [
                        'email'     => $providerData['email'],
                        'username'  => $username ,
                        'password'  => Str::random(10)
                    ];

                    $user = UserRepository::create($userData);
                }

                /* create provider record for user */
                $user->providers()->create(
                [
                    'provider_id' => $providerData['id'],
                    'provider_name' => $provider
                ]);
            }

            /* create user session */
            Auth::login($user);

            return redirect(config('services.social_failed_redirect'))->with(['message' => 'success']);
        }
        else
        {
            return redirect(config('services.social_failed_redirect'))->with(['error' => 'Authorization information is missing or invalid.']);
        }
    }

    /**
     * get detail of logged in user by bearer token
     * @return UserResource
     */
    public function getAuthUser()
    {
        $user = request()->user();

        if(!$user) {abort(404, 'User not found');}


        return new UserResource($user);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = UserRepository::updateUserInfo($request->validated());
        return new UserResource($user);
    }


    public function logout()
    {
        request()->user()->tokens()->delete();
        return response()->json(['message' => __('Logout successfully.')]);
    }
}
