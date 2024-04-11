<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Utils\ApiUtils;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Contest extends Model implements HasMedia
{
    use HasFactory,SoftDeletes,InteractsWithMedia;
    protected $fillable = [
        "contest_details",
        "name",
        "contest_code",
        "type",
        "image",
        "registration_allowed_until",
        "cancellation_allowed",
        "location_id",
        "on_tv",
        "contest_date",
        "winning_award",
        "rules",
        "start_time",
        "end_time",
        "status",
        'created_by',
        'updated_by'
    ] ;


    protected $hidden = [
        'created_by',
        'updated_by',
        'deleted_at',
        'created_at',
        'updated_at',
        'media'
    ];

    public static $rules = [];
    const IMAGE= 'image';

    public function questions()
    {
        return $this->hasMany(ContestQuestion::class);
    }

    public function enrolledContests()
    {
        return $this->hasMany(EnrolledContest::class);
    }
    
    public function getQuestionCountAttribute()
    {
        return $this->hasMany(ContestQuestion::class)->count();
    }

    public function getContestImageAttribute(): string
    {
        /** @var Media $media */
        $media = $this->getMedia(self::IMAGE)->first();
        return ! empty($media) ? $media->getFullUrl() : config('global.image_base_url')."/backend/default_image/logo.png";
    }

    public function getDurationAttribute()
    {
        $apiUtils = new ApiUtils();
        $endDateTime = Carbon::parse($this->end_time);
        $startDateTime = Carbon::parse($this->start_time);
        $duration = $endDateTime->diffInMinutes($startDateTime);
        return $apiUtils->convertDurationTime($duration);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strip_tags($value);
    }

    public function getNameAttribute($value)
    {
        return ucfirst($value);
    }
    public function getContestDetailsAttribute($value)
    {
        return str_replace("\r\n", "\n", $value);
    }
    public function getWinningAwardAttribute($value)
    {
        return str_replace("\r\n", "\n", $value);
    }
    public function getRulesAttribute($value)
    {
        return str_replace("\r\n", "\n", $value);
    }
}
