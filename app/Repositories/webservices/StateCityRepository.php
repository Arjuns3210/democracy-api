<?php

namespace App\Repositories\webservices;

//Common
use App\Models\State;
use App\Models\City;
use App\Utils\SearchScopeUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * Class StateCityRepository
 *
 * @package App\Repositories\webservices
 * @version Sep 25, 2023
 */
class StateCityRepository
{
    /**
     * Get Book Data
     *
     * @param  int  $contest_id
     * @return null
     */
    public function findWithoutFail($state_id)
    {
        try {
            $city = City::where('state_id',$state_id)->get();
            return $city;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getByRequest(array $attributes)
    {
        $contest = State::where('status', '1');
        $data['total_count'] = $contest->count();
        if (isset($attributes['paginate'])) {
            $data['result'] = $contest->paginate($attributes['paginate']);
        } else {
            $data['result'] = $contest->get();
        }
        return $data;
    }

}
