<?php

namespace App\Http\Controllers\webservices;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\UserNotificationResource;
use App\Repositories\webservices\UserNotificationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class UserNotificationApiController extends AppBaseController
{
     /** @var  UserNotificationRepository */
    private $userNotificationRepository;

    public function __construct(UserNotificationRepository $userNotificationRepo)
    {
        $this->userNotificationRepository = $userNotificationRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $input = $request->all();
            $input['uuid'] = $request->header('uuid') ?? '';
            $msg = trans('auth.data_fetched');
            $userNotification = $this->userNotificationRepository->getByRequest($input);
            if(count($userNotification['result']) == 0){
                $msg = trans('auth.notifications_empty');
            }
            
            return $this->sendResponse(UserNotificationResource::collection($userNotification['result']), $msg, $userNotification['total_count']);
        } catch(\Exception $e) {
            Log::info(json_encode($e->getMessage()));
            
            return $this->sendError(trans('auth.something_went_wrong'),500);
        }
    }
}
