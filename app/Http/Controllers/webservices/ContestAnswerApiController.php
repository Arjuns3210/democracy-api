<?php

namespace App\Http\Controllers\webservices;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\CreateContestAnswerApiRequest;
use App\Models\Answer;
use App\Models\Contest;
use App\Models\CorrectAnswer;
use App\Repositories\webservices\ContestAnswerRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Session;

class ContestAnswerApiController extends AppBaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $contestAnswerRepository;

    public function __construct(ContestAnswerRepository $contestRepo)
    {
        $this->contestAnswerRepository = $contestRepo;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateContestAnswerApiRequest $request)
    {
        try {
            DB::beginTransaction();
            $input = $request->all();
            $user_id = Session::get('userId');

            // Fetch start time, end time and contest date based on contest_id
            $contestDetails = Contest::find($input['contest_id']);
            

            $currentTime = Carbon::now();
            $start_time = Carbon::parse($contestDetails->contest_date . ' ' . $contestDetails->start_time);
            $end_time = Carbon::parse($contestDetails->contest_date . ' ' . $contestDetails->end_time);
            $contest_date = Carbon::parse($contestDetails->contest_date);

            if ($currentTime < $start_time || $currentTime > $end_time || $currentTime->format('Y-m-d') != $contest_date->format('Y-m-d')) {
                return $this->sendError(trans('auth.contest_not_active'), 200);
            }
            //To check if the user already submitted contest
            $previousSubmission = Answer::where('contest_id', $input['contest_id'])
            ->where('user_id', $user_id)
            ->exists();

            if ($previousSubmission) {
                return $this->sendError(trans('auth.contest_already_submitted'), 200);
            }
            $contestData = [];
            $questionCount = count($input['question_id']);
            for ($i = 0; $i < $questionCount ; $i++) {
                $contestData[] = [
                    'question_id' => $input['question_id'][$i] ?? '',
                    'option_id' => $input['option_id'][$i] ?? '',
                ];
            }
            foreach ($contestData as $value) {
                $contest = $this->contestAnswerRepository->create(['contest_id' => $input['contest_id'],'question_id' => $value['question_id'], 'option_id' => $value['option_id'],  'user_id' => $user_id,'answer_timing'=>$input['answer_timing']]);
                $previousSubmissionAnswers = Answer::where('contest_id', $input['contest_id'])
                    ->where('question_id', $value['question_id'])
                    ->get()
                    ->groupBy('option_id');
                $maxCountAnswer = [];
                foreach ($previousSubmissionAnswers as $answer) {
                    $maxCountAnswer[] = [
                        'total'           => count($answer),
                        'user_timing_sum' => $answer->sum('answer_timing'),
                        'option_id'       => $answer[0]->option_id ?? '',
                        'question_id'     => $answer[0]->question_id ?? '',
                    ];
                }
                $maxCountAnswer = collect($maxCountAnswer)->sortBy('user_timing_sum')->sortByDesc('total')->first();
                $duplicateCount = CorrectAnswer::where('contest_id', $input['contest_id'])
                    ->where('question_id', $maxCountAnswer['question_id'])
                    ->first();
               
            if (!empty($duplicateCount)) {
                $duplicateCount->update([
                    'answer_count' => $maxCountAnswer['total'],
                    'option_id'    => $maxCountAnswer['option_id'],
                ]);
            } else {
                CorrectAnswer::create([
                    'contest_id' => $input['contest_id'],
                    'question_id' => $value['question_id'],
                    'option_id' => $value['option_id'],
                    'answer_count' => 1,
                ]);
            }
        }
            DB::commit();
            return $this->sendResponse('', trans('auth.contest_submitted'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return $this->sendError(trans('auth.something_went_wrong'), 500);
        }
    }


}
