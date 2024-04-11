<?php

namespace App\Repositories\webservices;

//Common
use App\Models\Policy;
use Illuminate\Http\Request;

/**
 * Class PolicyRepository
 *
 * @package App\Repositories\webservices
 * @version Oct 01, 2023
 */
class PolicyRepository
{
    /**
     * Get Book Data
     *
     * @param  string  $type
     * @return null
     */
    public function findWithoutFail($type)
    {
        try {
            $policy = Policy::where('type', $type)->first();
            return $policy;
        } catch (\Exception $e) {
            return null;
        }
    }

}
