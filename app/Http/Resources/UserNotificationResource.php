<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserNotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // Replace 'trigger_date' with your actual property or variable containing the date string
        $notificationTriggerDateTime = Carbon::parse($this->trigger_date);
        $currentDateTime = Carbon::now();

// Calculate the time difference in a human-readable format
        $timeDifference = $notificationTriggerDateTime->diffForHumans($currentDateTime,true);
        
        $result = [
            "id"                     => $this->id,
            "title"                  => $this->title,
            "body"                   => $this->body,
            "notification_image_url" => $this->notification_image_url,
            "notification_type"      => $this->notification_type,
            "selected_id"            => $this->selected_id,
            "time_difference"        => $timeDifference,
        ];

        return $result;
    }
}
