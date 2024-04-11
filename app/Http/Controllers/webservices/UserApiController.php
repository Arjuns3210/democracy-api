<?php

namespace App\Http\Controllers\webservices;

use App\Http\Controllers\AppBaseController;
use App\Utils\ApiUtils;
use Illuminate\Http\Request;
use App\Repositories\webservices\UserRepository;
use App\Http\Requests\UpdateUserApiRequest;
use App\Http\Requests\UpdateUserAddressApiRequest;
use App\Http\Requests\UpdateFcmIdRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\Answer;
use Session;
use Carbon\Carbon;

class UserApiController extends AppBaseController
{
     /** @var  UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepository = $userRepo;
    }

    /**
     * Display the user profile data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        try {
            $input = $request->all();
            $input['id'] = Session::get('userId');
            $user = $this->userRepository->findWithoutFail($input['id']);
            if(empty($user)) {
                return $this->sendError(trans('auth.user_not_found'));
            }
            return $this->sendResponse(new UserResource($user), trans('auth.data_fetched'));
        } catch(\Exception $e) {
            \Log::info(json_encode($e->getMessage()));
            return $this->sendError(trans('auth.something_went_wrong'),500);
        }
    }

    /**
     * Update the user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserApiRequest $request)
    {
        try {
            $input = $request->all();
            $input['id'] = Session::get('userId');
            $input['dob'] = Carbon::parse($input['dob'])->format('Y-m-d');
            $user = $this->userRepository->findWithoutFail($input['id']);
            if(empty($user)) {
                return $this->sendError(trans('auth.user_not_found'));
            }
            $checkUser = User::where('phone', $input['phone'])
                             ->where('id', '!=', $input['id'])
                             ->first();
            if(!empty($checkUser)) {
                return $this->sendError(trans('validation.unique', array('attribute' => 'phone')));
            }
            if ($request->hasFile('user_image')) {
                $apiUtils = new ApiUtils();
                $apiUtils->clearMediaCollection($user, User::IMAGE);
                $apiUtils->storeMedia($user, $input['user_image'], User::IMAGE);
            }
            $result = $this->userRepository->update($input);
            return $this->sendResponse(new UserResource($result), trans('auth.user_updated'));
        } catch(\Exception $e) {
            \Log::info(json_encode($e->getMessage()));
            return $this->sendError(trans('auth.something_went_wrong'),500);
        }
    }

    public function updateAddress(UpdateUserAddressApiRequest $request)
    {
        try {
            $input = $request->all();
            $input['id'] = Session::get('userId');
            $user = $this->userRepository->findWithoutFail($input['id']);
            if(empty($user)) {
                return $this->sendError(trans('auth.user_not_found'));
            }
            $result = $this->userRepository->update($input);
            return $this->sendResponse(new UserResource($result), trans('auth.address_updated'));
        } catch(\Exception $e) {
            \Log::info(json_encode($e->getMessage()));
            return $this->sendError(trans('auth.something_went_wrong'),500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {
            $input = $request->all();
            $input['id'] = Session::get('userId');
            $user = $this->userRepository->findWithoutFail($input['id']);
            if(empty($user)) {
                return $this->sendError(trans('auth.user_not_found'));
            }
            $input['phone'] = $user->id.'del'.$user->phone;
            $contestEndTime = Answer::where('user_id', $user->id)->whereHas('contest', function ($query) {
            $query->whereRaw("(CONCAT(contest_date, ' ', end_time) > ?)", [Carbon::now()->format('Y-m-d H:i:s')]);
            })->get();

            if (!empty($contestEndTime) && count($contestEndTime) > 0) {
                return $this->sendError(trans('auth.contest_not_finished'));
            }
            $result = $this->userRepository->update($input);
            if($user->delete()) {
                UserDevice::where('user_id', $input['id'])->delete();
                $data['id'] = $input['id'];
                $data['updated_startup_data'] = $this->setStartupMeData(0);
                return $this->sendResponse($data, trans('auth.user_deleted'));
            }
        } catch(\Exception $e) {
            \Log::info(json_encode($e->getMessage()));
            return $this->sendError(trans('auth.something_went_wrong'),500);
        }
    }

    public function logoutUser(Request $request) {
        try {
            $uuid = $request->header('uuid');
            $userId = Session::get('userId');
            if($userId != 0) {
                UserDevice::where('user_id', $userId)
                          ->where('uuid', $uuid)
                          ->update([
                                'remember_token'=>null
                            ]);
            }
            $data['id'] = $userId;
            $data['updated_startup_data'] = $this->setStartupMeData(0);
            return $this->sendResponse($data, trans('auth.user_logout'));
        } catch (\Exception $e) {
            \Log::info(json_encode($e->getMessage()));
            return $this->sendError(trans('auth.something_went_wrong'),500);

        }
    }

    public function updateNotificationStatus(Request $request) {

        try {
            $user_id = Session::get('userId');
            $user = $this->userRepository->findWithoutFail($user_id);
            $status = $request->input('status');
            $fcmNotification = $status ? '1' : '0';

            if(empty($user)) {
                return $this->sendError(trans('auth.user_not_found'));
            }

            $user->fcm_notification = $fcmNotification;
            $user->save();

            return $this->sendResponse(array("id" => $user_id), trans('auth.notification_setting_updated'));

        } catch (\Exception $e) {
            \Log::info(json_encode($e->getMessage()));
            return $this->sendError(trans('auth.something_went_wrong'),500);
        }

    }

    public function updateFcmId(UpdateFcmIdRequest $request)
    {
        try {
            $userId = Session::get('userId');
            $uuid = $request->header('uuid');
            $input = $request->all();
            if($userId != 0) {
                $token = $request->header('access-token');
                $data = \JWTAuth::setToken($token)->getPayload();
                $expiry = $data['exp'];
                $expiryDateTime = Carbon::parse(date("Y-m-d", $expiry));
                $now = Carbon::now();
                if($now->diffInDays($expiryDateTime) <= 28) {
                    $checkToken = UserDevice::where('remember_token', $token)
                              ->where('uuid', $uuid)->first();
                    if(!empty($checkToken)) {
                        $newToken = \JWTAuth::refresh($token);
                        $checkToken->update([
                                        'remember_token'=>$newToken,
                                        'fcm_id'=>$input['fcm_token'] ?? ''
                                    ]);
                        return $this->sendRefreshToken(array("token" =>$newToken), trans('auth.token_expired'), 200, 3);
                    }
                }
                UserDevice::updateOrCreate(
                    ['user_id' => $userId, 'uuid' => $uuid],
                    ['fcm_id' => $input['fcm_token'] ?? '']
                );
            }

            return $this->sendResponse('',trans('auth.fcm_updated'));
        } catch (\Exception $e) {
            \Log::error("Update Fcm Id : ".$e->getMessage());

            return $this->sendError(trans('auth.something_went_wrong'), 500);
        }
    }
}
