<?php

namespace App\Http\Controllers\webservices;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\ShowContestAnswerApiRequest;
use App\Http\Requests\ShowContestPlayedApiRequest;
use App\Http\Resources\HomeResource;
use App\Http\Resources\ShowContestAnswerResultResource;
use App\Http\Resources\ShowContestPlayedResultResource;
use App\Repositories\webservices\HomeRepository;
use Illuminate\Http\Request;
use App\Http\Resources\ContactResource;
use App\Repositories\webservices\ContactRepository;
use App\Http\Requests\CreateContactApiRequest;
use Illuminate\Support\Facades\DB;

class HomeApiController extends AppBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $homeRepository;

    public function __construct(HomeRepository $homeRepo)
    {
        $this->homeRepository = $homeRepo;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $input = $request->all();
            $msg = trans('auth.data_fetched');
            $home = $this->homeRepository->getByRequest($input);
            if(count($home['result']) == 0) {
                $msg = trans('auth.home_not_found');
            }
            return $this->sendResponse(HomeResource::collection($home['result']), $msg, 0);
        } catch(\Exception $e) {
            \Log::info(json_encode($e->getMessage()));

            return $this->sendError(trans('auth.something_went_wrong'),500);
        }
    }

    /**
     *
     * @param  ShowContestPlayedApiRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function contestResultDetails(ShowContestPlayedApiRequest $request)
    {
        try {
            $input = $request->all();
            $contestPlayedData = $this->homeRepository->contestPlayedResultData($input);
            $msg = trans('auth.data_fetched');
            if(empty($contestPlayedData)) {
                $msg = trans('auth.contest_result_not_found');
            }
            
            return $this->sendResponse(new ShowContestPlayedResultResource($contestPlayedData), $msg);
        } catch(\Exception $e) {
            
            \Log::info(json_encode($e->getMessage()));
            return $this->sendError(trans('auth.something_went_wrong'),500);
        }
    }


    /**
     *
     * @param  ShowContestAnswerApiRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function contestAnswerDetails(ShowContestAnswerApiRequest $request)
    {
        try {
            $input = $request->all();
            $contestAnswerData = $this->homeRepository->contestPlayedAnswerData($input);
            $msg = trans('auth.data_fetched');
            if(empty($contestAnswerData)) {
                $msg = trans('auth.contest_result_answer_found');
            }

            return $this->sendResponse(new ShowContestAnswerResultResource($contestAnswerData), $msg);
        } catch(\Exception $e) {
            \Log::info(json_encode($e->getMessage()));
            return $this->sendError(trans('auth.something_went_wrong'),500);
        }
    }

}
