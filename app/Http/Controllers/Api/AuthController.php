<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;

class AuthController extends Controller
{
    public function signup(Request $request)
    {
        // $request->validate([
        //     'name' => 'required|string',
        //     'email' => 'required|string|email|unique:users',
        //     'phone' => 'required|string|unique:users',
        //     'user_name' => 'required|string|unique:users',
        //     'password' => 'required|string'
        // ]);

        $rules = [
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'phone' => 'required|string|unique:users',
            'username' => 'required|string|unique:users',
            'password' => 'required|string'
        ];
        // check for validation error
        $validator = \Validator::make($request->all(), $rules );
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error creating user!',
                'data' => $validator->errors()
            ]);
        }

       
        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'username' => $request->username,
            'password' => bcrypt($request->password)
        ]);

        $user->save();

        $avatar = Avatar::create($user->name)->getImageObject()->encode('png');
        Storage::put('avatars/'.$user->id.'/avatar.png', (string) $avatar);

        // $user->notify(new WelcomeNotification($user));
        // if(env('APP_ENV') == 'production'){
        //     Mail::to($user['email'])->send(new WelcomeMail($user));
        // }

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        $token->expires_at = Carbon::now()->addWeeks(1);

        $token->save();

        return response()->json([
            'status' => 'success',
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'user' => $user->load('cart')
        ], 201);
    }

    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        // $credentials = request(['email', 'password']);
        $email_login = User::where('email',$request->email)->first();
        $phone_login = User::where('phone',$request->email)->first();
        $username_login = User::where('username',$request->email)->first();

        $user;

        if($email_login){
            $user = $email_login;
        }elseif($phone_login){
            $user = $phone_login;
        }elseif($username_login){
            $user = $username_login;
        }else{
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);

        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
            'user' => $user->load('cart')
        ]);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'user' => $request->user()->load('cart')
        ]);
    }
}
