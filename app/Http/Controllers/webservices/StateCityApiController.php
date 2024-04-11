<?php

namespace App\Http\Controllers\webservices;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\StateCityApiRequest;
use App\Http\Requests\ShowCityListApiRequest;
use App\Http\Resources\StateCityResource;
use App\Http\Resources\ShowCityResource;
use App\Repositories\webservices\StateCityRepository;
use Illuminate\Http\JsonResponse;

class StateCityApiController extends AppBaseController
{
    /** @var  StateCityRepository */
    private $StateCityRepository;

    public function __construct(StateCityRepository $stateCityRepo)
    {
        $this->StateCityRepository = $stateCityRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(StateCityApiRequest $request)
    {
        try {
            $input = $request->all();
            $msg = trans('auth.data_fetched');
            $state = $this->StateCityRepository->getByRequest($input);
            if(count($state['result']) == 0) {
                $msg = trans('auth.state_not_found');
            }
            return $this->sendResponse(StateCityResource::collection($state['result']), $msg, $state['total_count']);
        } catch(\Exception $e) {
            \Log::info(json_encode($e->getMessage()));

            return $this->sendError(trans('auth.something_went_wrong'),500);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param  ShowCityListApiRequest  $request
     * @return JsonResponse`
     */
    public function citiesList(ShowCityListApiRequest $request)
    {
        try {
            $input['state_id'] = $request->state_id ?? '';
            $city = $this->StateCityRepository->findWithoutFail($input['state_id']);
            if(empty($city)) {
                return $this->sendError(trans('auth.city_not_found'));
            }

            return $this->sendResponse(new ShowCityResource($city), trans('auth.data_fetched'));
        } catch(\Exception $e) {
            \Log::info(json_encode($e->getMessage()));

            return $this->sendError(trans('auth.something_went_wrong'),500);
        }
    }


}
