<?php

namespace App\Http\Controllers\webservices;

use App\Http\Controllers\AppBaseController;
use App\Repositories\webservices\ContestRepository;
use App\Http\Resources\ListContestResource;
use App\Http\Resources\ShowContestResource;
use App\Http\Requests\ShowContestApiRequest;
use Illuminate\Http\Request;
use Session;

class ContestApiController extends AppBaseController
{
     /** @var  ContestRepository */
    private $contestRepository;

    public function __construct(ContestRepository $contestRepo)
    {
        $this->contestRepository = $contestRepo;
    }

    /**
     * Display a listing of the contest.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $input = $request->all();
            $msg = trans('auth.data_fetched');
            $contest = $this->contestRepository->getByRequest($input);

            if(count($contest['result']) == 0) {
                $msg = trans('auth.contests_not_found');
            }

            return $this->sendResponse(ListContestResource::collection($contest['result']), $msg, $contest['total_count']);
        } catch(\Exception $e) {
            \Log::info(json_encode($e->getMessage()));
            return $this->sendError(trans('auth.something_went_wrong'),500);
        }
    }

    /**
     * Display a specified Contest.
     *
     * @param  ShowContestApiRequest  $request
     * @return JsonResponse
     */
    public function show(ShowContestApiRequest $request)
    {
        try {
            $user_id = Session::get('userId');
            $input['id'] = $request->id;
            $contest = $this->contestRepository->findWithoutFail($input['id']);
            $contest['is_enrolled'] = $this->contestRepository->checkIsEnrolled($contest['id'], $user_id);
            return $this->sendResponse(new ShowContestResource($contest), trans('auth.data_fetched'));
        } catch(\Exception $e) {
            \Log::info(json_encode($e->getMessage()));
            return $this->sendError(trans('auth.something_went_wrong'),500);
        }
    }

}
