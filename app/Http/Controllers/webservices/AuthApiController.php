<?php

namespace App\Http\Controllers\webservices;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\UserRegistrationRequest;
use App\Http\Requests\UserOtpRequest;
use App\Http\Requests\ResendOtpRequest;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\Otp;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class AuthApiController extends AppBaseController
{


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(UserRegistrationRequest $request)
    {
        try {
            $input = $request->all();
            $uuid = $request->header('uuid');
            $checkUser = User::where('phone', $input['phone'])->first();
            $otpData['workflow'] = 'login';

            if (!empty($checkUser) && $checkUser->is_verified == 'Y') {
                if($checkUser->status != '1' ) {
                    return $this->sendError(trans('auth.user_account_not_active'));
                }
                if (!empty($input['fcm_token'])) {
                    UserDevice::updateOrCreate(
                        ['user_id' => $checkUser->id, 'uuid' => $uuid],
                        ['fcm_id' => $input['fcm_token'] ?? '']
                    );
                }

                //Create OTP
                $currentDateTime = Carbon::now();
                $expiry_time = date('Y-m-d H:i:s',(strtotime("$currentDateTime +  3 min")));
                $otpData['workflow'] = 'login';
                $otpData['expiry_time'] = $expiry_time;
                $otpData['mobile_no'] = $input['phone'];
                $otpData['mobile_no_with_code'] = '+91'.$input['phone'];
                $otpData['otp_code'] = $this->generateRandomString();
                $otpData['otp_verified'] = 'N';
                $otpChk = Otp::where([['mobile_no', $input['phone']]])->first();
                if(!empty($otpChk)) {
                    $last_count = $otpChk->verify_count;
                    $last_hitting_time = $otpChk->updated_at;
                    $next_1_hour_time = (strtotime("$last_hitting_time +  1 hour"));
                    $current_time =  time();
                    $new_count = 0;
                    if($current_time > $next_1_hour_time || $last_count < 3) {
                        $new_count = $last_count+1;
                        if($new_count > 3){
                            $new_count=1;
                        }
                    } else {
                        $new_count=1;
                    }
                    $new_count = $last_count+1;
                    if($new_count > 3){
                        $new_count=1;
                    }
                    $otpData['verify_count'] = $new_count;
                    Otp::where('mobile_no', $otpData['mobile_no'])->update($otpData);
                } else {
                    $otpData['verify_count'] = 1;
                    Otp::create($otpData);
                }

                //Frame and Send response
                $userData['id'] = $checkUser->id;
                $userData['phone'] = $checkUser->phone;
                $userData['name'] = $checkUser->name;

                if(config('global.sms_send')) {
                    $result = $this->sendOTPMessage($otpData['otp_code'], $otpData['mobile_no']);
                    if(!$result) {
                        \Log::error("OTP sending failed - SMS credentials not set");
                    } else if(is_string($result) && is_object(json_decode($result))) {
                        $data = json_decode($result);
                        if($data->status == "error") {
                            \Log::error("OTP sending failed - ".$data->message);
                        }
                    }
                }

                return $this->sendResponse($userData, trans('auth.otp_sent'));
            }

            if (empty($checkUser)) {
                $input['name'] = 'App User';
                $input['approved_on'] = Carbon::now();
                $input['whatsapp_no'] = $input['phone'];
                $userData = User::create($input);
            } else {
                $userData = $checkUser;
            }

            if (!empty($input['fcm_token'])) {
                UserDevice::updateOrCreate(
                    ['user_id' => $userData->id, 'uuid' => $uuid],
                    ['fcm_id' => $input['fcm_token'] ?? '']
                );
            }

            //Create OTP
            $currentDateTime = Carbon::now();
            $otpData['expiry_time'] = date('Y-m-d H:i:s',(strtotime("$currentDateTime +  3 min")));
            $otpData['mobile_no'] = $input['phone'];
            $otpData['mobile_no_with_code'] = '+91'.$input['phone'];
            $otpData['otp_code'] = $this->generateRandomString();
            $otpData['otp_verified'] = 'N';
            $otpChk = Otp::where([['mobile_no', $input['phone']]])->first();
            if(!empty($otpChk)) {
                $last_count = $otpChk->verify_count;
                $last_hitting_time = $otpChk->updated_at;
                $next_1_hour_time = (strtotime("$last_hitting_time +  1 hour"));
                $current_time =  time();
                $new_count = 0;
                if($current_time > $next_1_hour_time || $last_count < 3) {
                    $new_count = $last_count+1;
                    if($new_count > 3){
                        $new_count=1;
                    }
                } else {
                    $new_count=1;
                }
                $new_count = $last_count+1;
                if($new_count > 3){
                    $new_count=1;
                }
                $otpData['verify_count'] = $new_count;
                Otp::where('mobile_no', $otpData['mobile_no'])->update($otpData);
            } else {
                $otpData['verify_count'] = 1;
                Otp::create($otpData);
            }

            if(config('global.sms_send')) {
                $result = $this->sendOTPMessage($otpData['otp_code'], $otpData['mobile_no']);
                if(!$result) {
                    \Log::error("OTP sending failed - SMS credentials not set");
                } else if(is_string($result) && is_object(json_decode($result))) {
                    $data = json_decode($result);
                    if($data->status == "error") {
                        \Log::error("OTP sending failed - ".$data->message);
                    }
                }
            }

            return $this->sendResponse(Arr::only($userData->toArray(), ['id', 'phone', 'name']), trans('auth.otp_sent'));
        } catch (\Exception $e) {

            \Log::error("Registration failed: " . $e->getMessage());
            return $this->sendError(trans('auth.something_went_wrong'), 500);
        }
    }

    /**
     * Validate OTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function validateOtp(UserOtpRequest $request)
    {
        try {
            $input = $request->all();
            if(!empty($request->otp_code) && isset($input['otp_code'])){
                $otpChk = Otp::where([['otp_code', $input['otp_code']],['mobile_no', $input['mobile_number']],['verify_count', '<=', '3']])->get();
                $platform = $request->header('platform') ?? '';
                if(count($otpChk) > 0 && $otpChk[0]->otp_verified == 'N') {
                    $currentDateTime = Carbon::now();
                    if(strtotime($currentDateTime) > strtotime($otpChk[0]->expiry_time)){
                        return $this->sendError(trans('auth.otp_expired'));
                    }
                    $otps = Otp::find($otpChk[0]->id);
                    $otps->otp_verified = 'Y';
                    $otps->save();
                    User::where('phone', $input['mobile_number'])->update(
                        ['is_verified' => 'Y']
                    );

                    $user = User::where('phone', $input['mobile_number'])->first();
                    //Create token and save
                    $uuid = $request->header('uuid');
                    $token = JWTAuth::fromUser($user);

                    UserDevice::updateOrCreate(
                        ['user_id' => $user->id, 'uuid' => $uuid],
                        ['remember_token' => $token, 'platform'=>$platform]
                    );

                    $user['remember_token'] = $token;

                    $user['updated_startup_data'] = $this->setStartupMeData(1);
                    $msg = trans('auth.loggedin_successfully');

                    return $this->sendResponse($user, $msg);
                } else{
                    //otp expired
                    return $this->sendError(trans('auth.invalid_otp'));
                }
            }else{
                return $this->sendError(trans('auth.please_enter_otp_code'));
            }
        } catch (\Exception $e) {
            \Log::error("OTP Verification failed: " . $e->getMessage());
            return $this->sendError(trans('auth.something_went_wrong'), 500);
        }
    }

    /**
     * Resend OTP.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resendOtp(ResendOtpRequest $request)
    {
        try {
            $input = $request->all();
            $checkUser = User::where('phone', $input['phone'])->first();

            if (empty($checkUser)) {
                return $this->sendError(trans('auth.account_not_registered'));
            }

            //Create OTP
            $currentDateTime = Carbon::now();
            $expiry_time = date('Y-m-d H:i:s',(strtotime("$currentDateTime +  3 min")));
            $otpData['expiry_time'] = $expiry_time;
            $otpData['mobile_no'] = $input['phone'];
            $otpData['mobile_no_with_code'] = '+91'.$input['phone'];
            $otpData['otp_code'] = $this->generateRandomString();
            $otpData['otp_verified'] = 'N';
            $otpChk = Otp::where([['mobile_no', $input['phone']]])->first();
            if(!empty($otpChk)) {
                $last_count = $otpChk->verify_count;
                $last_hitting_time = $otpChk->updated_at;
                $next_1_hour_time = (strtotime("$last_hitting_time +  1 hour"));
                $current_time =  time();
                $new_count = 0;
                if($current_time > $next_1_hour_time || $last_count < 3) {
                    $new_count = $last_count+1;
                    if($new_count > 3){
                        $new_count=1;
                    }
                } else {
                    $new_count=1;
                }
                $new_count = $last_count+1;
                if($new_count > 3){
                    $new_count=1;
                }
                $otpData['verify_count'] = $new_count;
                Otp::where('mobile_no', $otpData['mobile_no'])->update($otpData);
            } else {
                $otpData['verify_count'] = 1;
                Otp::create($otpData);
            }
            //Frame and Send response
            $userData['id'] = $checkUser->id;
            $userData['phone'] = $checkUser->phone;
            $userData['name'] = $checkUser->name;
            $userData['otp_code'] = $otpData['otp_code'];

            if(config('global.sms_send')) {
                $result =$this->sendOTPMessage($userData['otp_code'], $userData['phone']);
                if(!$result) {
                    \Log::error("OTP sending failed - SMS credentials not set");
                } else if(is_string($result) && is_object(json_decode($result))) {
                    $data = json_decode($result);
                    if($data->status == "error") {
                        \Log::error("OTP sending failed - ".$data->message);
                    }
                }
            }

            return $this->sendResponse($userData, trans('auth.otp_sent'));
        } catch (\Exception $e) {
            \Log::error("OTP Verification failed: " . $e->getMessage());
            return $this->sendError(trans('auth.something_went_wrong'), 500);
        }
    }
}
