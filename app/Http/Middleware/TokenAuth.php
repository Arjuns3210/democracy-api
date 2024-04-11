<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Session;
use App\Utils\ResponseMessageUtils;
use App\Models\UserDevice;
use App\Models\User;

class TokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $tokenData = '1';
        try {
            $token = $request->header('access-token');
            $uuid = $request->header('uuid');
            $data = JWTAuth::setToken($token)->getPayload();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            if(!in_array(\Request::route()->getName(), config('global.non_mandatory_token'))) {
                return ResponseMessageUtils::sendError(__('auth.token_expired'), 200, 4);
            } else {
                $tokenData = '0';
            }
            // echo json_encode($return_array);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            if(!in_array(\Request::route()->getName(), config('global.non_mandatory_token'))) {
                $routeName = \Request::route()->getName();

                if ($routeName == 'contest.enrolling' || $routeName == 'contest.start_playing') {
                    return ResponseMessageUtils::sendError(__('auth.enrolling_without_login'), 200, 4);
                } else {
                    return ResponseMessageUtils::sendError(__('auth.authentication_failed'), 200, 4);
                }
            } else {
                $tokenData = '0';
            }
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            if(!in_array(\Request::route()->getName(), config('global.non_mandatory_token'))) {
                return ResponseMessageUtils::sendError(__('auth.authentication_failed'), 200, 4);
            } else {
                $tokenData = '0';
            }
        }

        if($tokenData == '1') {
            $userChk = UserDevice::where([['user_id', $data['sub']], ['uuid', $uuid]])->get();
            if (count($userChk) == 0 || $userChk[0]->remember_token == '') {
                if(!in_array(\Request::route()->getName(), config('global.non_mandatory_token'))) {
                    return ResponseMessageUtils::sendError(__('auth.please_login_and_try_again'), 200, 4);
                } else {
                    $uid = 0;
                }
            } else {
                $user = User::find($data['sub']);
                if ($user && $user->status == 0) {
                    $user->token = "";
                    return ResponseMessageUtils::sendError(__('auth.authentication_failed'), 200, 4);
                } else {
                    $uid = $data['sub'];
                }
            }
        } else {
            $uid = 0;
        }
        Session::flash('userId', $uid);

        return $next($request);
    }
}
