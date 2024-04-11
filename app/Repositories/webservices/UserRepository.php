<?php

namespace App\Repositories\webservices;

//Common
use Carbon\Carbon;
use App\Models\User;

/**
 * Class UserRepository
 *
 * @package App\Repositories\webservices
 * @version Sep 23, 2023
 */
class UserRepository
{

    /**
     * @param $id
     * @param array $columns
     * @return mixed|null
     */
    public function findWithoutFail($id)
    {
        try {
            $user = User::find($id);
            return $user;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Update User
     *
     * @param array $attributes
     */
    public function update(array $attributes)
    {
        try {
            \DB::beginTransaction();
            /** @var User $user */
            $user = User::find($attributes['id']);
            $success = $user->update($attributes);
            \DB::commit();
            return $user;
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($e);
            throw $e;
        }
    }
    


}
