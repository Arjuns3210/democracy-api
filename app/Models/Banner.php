<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;


class Banner extends Model implements HasMedia

{
    use SoftDeletes, HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'image',
        'created_by',
        'updated_by',
    ];

    const IMAGE= 'image';

    public function getBannerImageAttribute(): string
    {
        /** @var Media $media */
        $media = $this->getMedia(self::IMAGE)->first();
        return ! empty($media) ? $media->getFullUrl() : config('global.image_base_url')."backend/default_image/logo.png";
    }

}
