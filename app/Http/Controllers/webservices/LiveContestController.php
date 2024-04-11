<?php

namespace App\Http\Controllers\webservices;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\LiveContestResource;
use App\Http\Requests\ShowLiveContestApiRequest;
use App\Models\Contest;
use App\Models\EnrolledContest;
use App\Models\ContestQuestion;
use Carbon\Carbon;
use Session;
use Illuminate\Support\Facades\DB;
use App\Repositories\webservices\LiveContestRepository;
use Illuminate\Http\JsonResponse;

class LiveContestController extends AppBaseController
{
    
    /**
     * Display the mapped question.
     *
     * @param  ShowLiveContestApiRequest  $request
     * @return JsonResponse
     */
    public function startPlaying(ShowLiveContestApiRequest $request)
    {
        try {
            DB::beginTransaction();
            $msg = trans('auth.data_fetched');
            $input = $request->all();
            $input['user_id'] = Session::get('userId');

            $checkUser = EnrolledContest::where('contest_id', $input['contest_id'])
                ->where('user_id', $input['user_id'])
                ->first();
            if (empty($checkUser)) {
                $enrolledData = [
                    'user_id'    => $input['user_id'],
                    'contest_id' => $input['contest_id'],
                ];
                EnrolledContest::create($enrolledData);
            }
             
            $contest = Contest::where('id',$input['contest_id'])->first();
            $nowDay = Carbon::now()->format('Y-m-d');
            $nowTime = Carbon::now()->format('H:i:s');
            if ($contest->contest_date != $nowDay || $contest->start_time >= $nowTime || $contest->end_time <= $nowTime) {
                return $this->sendError(trans('auth.contest_time_mismatch'));
            }
            
            $contestQuestions = ContestQuestion::with('question')->where('contest_id', $input['contest_id'])->orderBy('sequence')->get();
            $mappedQuestion = [];
            foreach($contestQuestions as $key => $contestQuestion){
                $mappedQuestion[] = [
                    'question_id' => $contestQuestion->question->id,
                    'question'    => $contestQuestion->question->question ?? '',
                    'options'     => $contestQuestion->question->option ?? [],
                ];
            }
            
            $data['question'] = $mappedQuestion;
            if(count($data['question']) == 0){
                return $this->sendError(trans('auth.question_not_mapped'));
            }
            DB::commit();
          
            return $this->sendResponse(LiveContestResource::collection($data['question']), $msg,0);

        } catch (\Exception $e) {
            \Log::info(json_encode($e->getMessage()));

            return $this->sendError(trans('auth.something_went_wrong'), 500);
        }
    }

}
