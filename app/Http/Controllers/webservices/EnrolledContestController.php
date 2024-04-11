<?php

namespace App\Http\Controllers\webservices;

use App\Http\Controllers\AppBaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\CreateEnrolledContestApiRequest;
use App\Http\Resources\EnrollingContestResource;
use App\Http\Resources\ListEnrollContestResource;
use App\Repositories\webservices\EnrolledContestRepository;
use Illuminate\Http\JsonResponse;
use App\Models\EnrolledContest;
use App\Models\Contest;
use Session;

class EnrolledContestController extends AppBaseController
{
    /** @var  EnrolledContestRepository */
    private $EnrolledContestRepository;
    public function __construct(EnrolledContestRepository $enrolledContestRepo)
    {
        $this->EnrolledContestRepository = $enrolledContestRepo;
    }

    /**
     * To Enrolled Contest List.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $input = $request->all();
            $msg = trans('auth.data_fetched');
            $enrolledContest = $this->EnrolledContestRepository->getByRequest($input);
            if(count($enrolledContest['result']) == 0) {
                $msg = trans('auth.enrolled_contest_not_found');
            }

            return $this->sendResponse(ListEnrollContestResource::collection($enrolledContest['result']), $msg, $enrolledContest['total_count']);
        } catch(\Exception $e) {
            \Log::info(json_encode($e->getMessage()));

            return $this->sendError(trans('auth.something_went_wrong'),500);
        }
    }

    /**
     * Store a newly Enrolled in Contest.
     *
     * @param  CreateEnrolledContestApiRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateEnrolledContestApiRequest $request)
    {
        try {
            $input = $request->all();
            $input['user_id'] = Session::get('userId');

            $contest = Contest::where('id', $input['contest_id'])->first();
            $nowDay = Carbon::now()->format('Y-m-d');
            $nowTime = Carbon::now()->format('H:i:s');

            if ($contest->registration_allowed_until < $nowDay || ($contest->registration_allowed_until == $nowDay && $contest->end_time < $nowTime)) {
                return $this->sendError(trans('auth.enrolling_closed'));
            }

            if ($contest->registration_start_date > $nowDay) {
                return $this->sendError(trans('auth.enrolling_not_started'));
            }
            
            $checkUser = EnrolledContest::where('contest_id', $input['contest_id'])
                             ->where('user_id', $input['user_id'])
                             ->first();
            if(!empty($checkUser)) {
                return $this->sendError(trans('auth.already_enrolled'));
            }
            $result = $this->EnrolledContestRepository->create($input);
            return $this->sendResponse(new EnrollingContestResource($result), trans('auth.enroll_success'));
        } catch(\Exception $e) {
            \Log::info(json_encode($e->getMessage()));
            return $this->sendError(trans('auth.something_went_wrong'),500);
        }
    }
}
