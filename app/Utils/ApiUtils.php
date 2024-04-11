<?php
/*
    *   Developed by : Nikunj - Mypcot Infotech
    *   Created On : 27-09-2023
    *   https://www.mypcot.com/
*/
namespace App\Utils;

class ApiUtils
{
    public function convertDurationTime($durationInMinutes)
    {
        if ($durationInMinutes >= 60) {
            $durationInHours = floor($durationInMinutes / 60);
            $remainingMinutes = $durationInMinutes % 60;
            $formattedDuration = $durationInHours == 1 ? $durationInHours. ' hr' : $durationInHours.' hrs';

            if ($remainingMinutes > 0) {
                $formattedDuration .= ' ' . $remainingMinutes . ' mins';
            } else {
                $formattedDuration .= '';
            }
        } else {
            $formattedDuration = $durationInMinutes . ' mins';
        }
        return $formattedDuration;
    }

    public function storeMedia($model, $file, $collectionName)
    {
        $extension = $file->getClientOriginalExtension();
        $fileName = 'user_' . time() . '.' . $extension;

        $model->addMedia($file)->usingFileName($fileName)->toMediaCollection($collectionName, config('app.media_disc'));

        return true;
    }

    public function clearMediaCollection($user, $collectionName)
    {
        if ($user->hasMedia($collectionName)) {
            $media = $user->getMedia($collectionName);

            foreach ($media as $mediaItem) {
                $mediaItem->delete();
            }
        }

        return response()->json(['message' => 'Media collection cleared successfully']);
    }
}
