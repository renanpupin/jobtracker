<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Passwords\PasswordResetServiceProvider;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth;
use Mockery\CountValidator\Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;
use App\PasswordReset;
use Illuminate\Support\Facades\Password;
use Message;
use Mail;

class AuthenticateController extends Controller
{

    public function __construct()
    {
        // Apply the jwt.auth middleware to all methods in this controller
        // except for the authenticate, resetEmail and resetConfirm method. We don't want to prevent
        // the user from retrieving their token if they don't already have it
        $this->middleware('jwt.auth', ['except' => ['authenticate', 'resetEmail', 'resetConfirm']]);
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resetEmail(Request $request)
    {

        $email = $request->only('email')['email'];

        $hasUser = User::where('email', $email)->count() > 0 ? true : false;

        if(!$hasUser){
            return response()->json(['status' => 'wrong_email'], 400);
        }else{

            try{
                $token = bin2hex(random_bytes(50));

                $hasExistingToken = PasswordReset::where('email', $email)->count() > 0 ? true : false;

                if($hasExistingToken){
                    $password_reset = PasswordReset::where('email', $email)->first();
                    $password_reset->token = $token;
                    $password_reset->timestamps = false;
                    $password_reset->save();
                }else{
                    PasswordReset::create([
                        'email' => $email,
                        'token' => token
                    ]);
                }

                Mail::send('email.reset_password',
                    [
                        'email' => $email,
                        'token' => $token
                    ],
                    function ($message) use ($email) {
                        $message->from('api@email.com', 'API');
                        $message->to($email)->subject('Assunto API');
                    }
                );

                return response()->json(['status' => 'email_sent'], 200);

            }catch (Exception $e){
                return response()->json(['status' => 'error_sending_reset_email'], 500);
            }

        }
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resetConfirm(Request $request, $token)
    {
        $email = $request->get('email');
        $password = $request->get('password');

        if($token == null || $email == null || $password == null){
            return response()->json(['status' => 'reset_data_not_found'], 400);
        }else{

            $user = User::where('email', $email)->first();

            if($user->count() == 0){
                return response()->json(['status' => 'user_not_found'], 400);
            }else{
                try{
                    $password_reset = PasswordReset::where('email', $email)->first();

                    if($password_reset->count() == 0){
                        return response()->json(['status' => 'password_reset_not_found'], 400);
                    }else if($token == $password_reset->token){

                        $user->password = bcrypt($password);
                        $user->save();

                        $password_reset->delete();

                        return response()->json(['status' => 'password_changed'], 200);
                    }else{
                        return response()->json(['status' => 'wrong_token'], 400);
                    }
                }catch (Exception $e){
                    return response()->json(['status' => 'error_on_password_change'], 500);
                }

            }
        }

    }

    public function getAuthenticatedUser()
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('user'));
    }

    public function index()
    {
        // Retrieve all the users in the database and return them
        $users = User::all();
        return $users;
    }

    function register(Request $request) {
        $credentials = Input::only('email', 'password');

        try {
            $user = User::create($credentials);
        } catch (Exception $e) {
            return Response::json(['error' => 'User already exists.'], HttpResponse::HTTP_CONFLICT);
        }

        $token = JWTAuth::fromUser($user);

        return Response::json(compact('token'));
    }

    /*protected function isUsingThrottlesLoginsTrait()
    {
        return in_array(
            ThrottlesLogins::class, class_uses_recursive(get_class($this))
        );
    }*/

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        //$this->username = $request->email;

        /*$throttles = $this->isUsingThrottlesLoginsTrait();
        if ($throttles && $this->hasTooManyLoginAttempts($request)) {
            return response()->json(['error' => 'too_many_attempts'], 401);
        }*/

        try {
            // verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                /*if ($throttles) {
                    $this->incrementLoginAttempts($request);
                }*/
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // if no errors are encountered we can return a JWT
        return response()->json(compact('token'));
    }

    public function logout(Request $request)
    {
        try{
            JWTAuth::invalidate(JWTAuth::getToken());
            return response()->json(['message' => 'ok']);
        } catch (JWTException $e) {
            return response()->json(['message' => 'error']);
        }
    }
}
