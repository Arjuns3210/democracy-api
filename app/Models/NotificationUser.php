<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationUser extends Model
{
    public $fillable = [
        'notification_id',
        'user_id',
        'is_read',
        'batch_number',
        'send_date',
        'user_device_id',
        'is_send',
        'response',
        'trigger_date',
        'attempt',
        'title',
        'body'
    ];
    public $timestamps = false;
    public $casts = [
        'notification_id' =>'integer',
        'user_id' =>'integer',
        'is_read' =>'boolean',
        'batch_number'=>'integer',
        'send_time'=>'datetime'
    ];

    public function userDevice()
    {
        return $this->belongsTo(UserDevice::class,'user_device_id');
    }

    public function notification()
    {
        return $this->belongsTo(Notification::class,'notification_id');
    }

    public function getNotificationImageUrlAttribute(): string
    {
        return $this->notification()->first()->image_url ?? '';
    }
    public function getNotificationTypeAttribute(): string
    {
        return $this->notification()->first()->type ?? '';
    }
    public function getSelectedIdAttribute()
    {
        return $this->notification()->first()->selected_id ?? '';
    }
}
