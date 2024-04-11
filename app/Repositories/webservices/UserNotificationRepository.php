<?php

namespace App\Repositories\webservices;

use App\Models\NotificationUser;
use App\Models\UserDevice;
use App\Utils\SearchScopeUtils;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

/**
 * Class UserNotificationRepository
 *
 * @package App\Repositories\webservices
 * @version fab 08, 2024
 */
class UserNotificationRepository
{
    /**
     * Get UserNotification
     *
     * @param  array  $attributes
     * @return array
     */
    public function getByRequest(array $attributes) {

        $userId = Session::get('userId');
        $uuid = $attributes['uuid'];
        $userDeviceIds = UserDevice::where('uuid',$uuid)->where('user_id',$userId)->pluck('id')->toArray();
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $userNotification = NotificationUser::where('user_id', $userId)
            ->whereIn('user_device_id', $userDeviceIds)
            ->where('is_send', '1')->where('trigger_date', '<=', $now);
        
        $orderBy = $attributes['order_by'] ?? 'notification_users.send_date';
        $sortBy = $attributes['sort_by'] ?? 'desc';

        $userNotification->orderBy($orderBy, $sortBy);
        
        $data['total_count'] = $userNotification->count();
        if (isset($attributes['paginate'])) {
            $data['result'] = $userNotification->paginate($attributes['paginate']);
        } else {
            
            $data['result'] = $userNotification->get();
        }
        return $data;
    }
}
