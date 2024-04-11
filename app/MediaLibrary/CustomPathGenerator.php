<?php

namespace App\MediaLibrary;

use App\Models\User;
use App\Models\Contest;
use App\Models\Notification;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

/**
 * Class CustomPathGenerator
 */
class CustomPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        $path = '{PARENT_DIR}'.DIRECTORY_SEPARATOR.$media->id.DIRECTORY_SEPARATOR;

        switch ($media->collection_name) {
            case Contest::IMAGE;
               return str_replace('{PARENT_DIR}', Contest::IMAGE, $path);
            case Notification::NOTIFICATION_IMAGE;
                return str_replace('{PARENT_DIR}', Notification::NOTIFICATION_IMAGE, $path);
            case User::IMAGE;
                return str_replace('{PARENT_DIR}', User::IMAGE, $path);
            case 'default';
                return '';
        }
    }

    /**
     * @param  Media  $media
     *
     * @return string
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media).'thumbnails/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media).'rs-images/';
    }
}
