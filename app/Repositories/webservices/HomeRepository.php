<?php

namespace App\Repositories\webservices;

//Common
use App\Models\Answer;
use App\Models\Banner;
use App\Models\Contest;
use App\Models\ContestQuestion;
use App\Models\CorrectAnswer;
use App\Models\QuestionOption;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;


/**
 * Class HomeRepository
 *
 * @package App\Repositories\webservices
 * @version jan 23, 2024
 */
class HomeRepository
{
    public function getByRequest(array $attributes)
    {
        $homeTypes = [
            'banner',
            'live_contest',
            'recently_played',
            'featured_contest',
            'win_big_this_week',
        ];

        $homeData = [];
        $homeData['result'] = [];
        foreach ($homeTypes as $homeType) {
            if ($homeType == 'banner'){
                $banner = Banner::first();
                $homeData['result'][] = [
                    'type' =>'banner',
                    'title' =>'',
                    'collection_data' => [],
                    'banner_image'=>$banner->banner_image
                ];
            }
            
            if ($homeType == 'live_contest'){
                $nowDay = Carbon::now()->format('Y-m-d');
                $nowTime = Carbon::now()->format('H:i:s');
                $minEnrolledContestCount = config('global.enrolled_contests_count');
                $contests = Contest::where('status','1')
                    ->whereHas('questions')
                    ->withCount('enrolledContests')
                    ->has('enrolledContests','>=',$minEnrolledContestCount)
                    ->where('contest_date',$nowDay)
                    ->where('start_time', '<=', $nowTime)
                    ->where('end_time', '>=', $nowTime)
                    ->get();

                $contestCount = $contests->count();
                $contests = $contests->take(config('global.home_data_limit'));
                
                $collectionData = [];
                foreach ($contests as $contest){
                    $collectionData[] = [
                        'contest_id' => $contest->id,
                        'contest_name'       => $contest->name,
                        'total_question'     => $contest->question_count ?? 0,
                    ];
                }
                $homeData['result'][] = [
                    'title' =>'Live Contest',
                    'type' =>'live_contest',
                    'image_url' => asset('backend/default_image/live_contest.png'),
                    'collection_data' => $collectionData,
                    'view_all' => $contestCount > config('global.home_data_limit'),
                ];
            }
            
            if ($homeType == 'recently_played') {
                $collectionData = $this->getRecentlyPlayedData();
               
                $homeData['result'][] = [
                    'type'            => 'recently_played',
                    'title'            => 'Recently Played',
                    'image_url' => asset('backend/default_image/recently_played.png'),
                    'collection_data' => $collectionData,
                ];
            }

            if ($homeType == 'featured_contest'){
                $nowDay = Carbon::now()->format('Y-m-d');
                $targetDateTime = Carbon::now()->addDays(7)->format('Y-m-d H:i:s');
               
                $contests = Contest::where('status','1')
                    ->whereHas('questions')
                    ->where('registration_start_date', '<=', $nowDay)
                    ->where('contest_date', '>', $targetDateTime)
                     ->get();
                $contestCount = $contests->count();
                $contests = $contests->take(config('global.home_data_limit'));
                $collectionData = [];
                foreach ($contests as $contest){
                    $collectionData[] = [
                        'contest_id' => $contest->id,
                        'contest_name' => $contest->name,
                        'sub_title'    => $contest->sub_title,
                        'contest_time_start'    => Carbon::parse($contest->start_time)->format('h:i A'),
                        'contest_date'    => Carbon::parse($contest->contest_date)->format('jS M y'),
                        'total_question'=> $contest->question_count ?? 0,
                    ];
                }
                $homeData['result'][] = [
                    'type' =>'featured_contest',
                    'title' =>'Featured Contest',
                    'collection_data' => $collectionData,
                    'view_all' => $contestCount > config('global.home_data_limit'),
                ];
            }


            if ($homeType == 'win_big_this_week'){
                $targetDateTime = Carbon::now()->addDays(7);
                $nowDateTime = Carbon::now()->format('Y-m-d H:i:s');
                $contests = Contest::where('status','1')
                    ->whereHas('questions')
                    ->where('registration_start_date', '<=', $nowDay)
                    ->whereRaw("(CONCAT(contest_date, ' ', start_time) >= ?)", [$nowDateTime])
                    ->whereRaw("(CONCAT(contest_date, ' ', start_time) <= ?)", [$targetDateTime->format('Y-m-d H:i:s')])
                    ->get();
                
                $contestCount = $contests->count();
                $contests = $contests->take(config('global.home_data_limit'));
                $collectionData = [];
                foreach ($contests as $contest){
                    $collectionData[] = [
                        'contest_id' => $contest->id,
                        'contest_name' => $contest->name,
                        'sub_title'    => $contest->sub_title,
                        'contest_time_start'    => Carbon::parse($contest->start_time)->format('h:i A'),
                        'contest_date'    => Carbon::parse($contest->contest_date)->format('jS M y'),
                        'total_question'=> $contest->question_count ?? 0,
                    ];
                }
              
                $homeData['result'][] = [
                    'type' =>'win_big_this_week',
                    'title' =>'Win Big This Week',
                    'collection_data' => $collectionData,
                    'view_all' => $contestCount > config('global.home_data_limit'),
                ];
            }
        }

        $homeData['result'] = array_filter($homeData['result'], function ($item) {
            return $item['type'] === 'banner' || ( $item['type'] !== 'banner' && !empty($item['collection_data']) );
        });

        return $homeData;
    }

    /**
     *
     * @return array
     */
    public function getRecentlyPlayedData(): array
    {
        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        $userId = Session::get('userId');
        $answers = Answer::with('contest')
            ->whereHas('contest', function ($query) {
                $query->where('contest_date', Carbon::now()->format('Y-m-d'))
                    ->where('end_time', '<=', Carbon::now()->format('H:i:s'));
            })
            ->where('user_id', $userId)
//            ->whereBetween('created_at',[$startDate, $endDate])
            ->latest()
            ->orderBy('answer_timing')
            ->get()
            ->groupBy(['contest_id'])
            ->take(config('global.recently_played_limit'));
        $data = [];
        foreach ($answers as $contestId => $contestQuestions) {
            $data[$contestId] = [];
            $totalQuestions = count($contestQuestions);
            $trueQuestions = 0;
            foreach ($contestQuestions as $userId => $userQuestions) {
                $majorityQuestionAnswer = CorrectAnswer::where('contest_id',
                    $userQuestions->contest_id)
                    ->where('question_id', $userQuestions->question_id)
                    ->orderBy('answer_count','desc')
                    ->first();
                if ($majorityQuestionAnswer->option_id == $userQuestions->option_id ?? 0) {
                    $trueQuestions += 1;
                }

                $data[$contestId] = [
                    'contest_name' => $userQuestions->contest->name ?? '',
                    'contest_id' => $userQuestions->contest_id ?? '',
                ];

                $totalPercentage = $trueQuestions * 100 / $totalQuestions;
                $totalPercentage = (double) number_format($totalPercentage, 2);
                $data[$contestId]['percentage'] = $totalPercentage."%";
            }
        }
        
        return $data;
    }

    /**
     * @param $request
     *
     * @return array
     */
    public function contestPlayedResultData($request)
    {
        
        $userId = Session::get('userId');
        $contestId = $request['contest_id'];
        $answers = Answer::with('contest')
            ->whereHas('contest', function ($query) {
                $query->whereRaw("(CONCAT(contest_date, ' ', end_time) < ?)", [Carbon::now()->format('Y-m-d H:i:s')]);
            })
            ->where('user_id', $userId)
            ->where('contest_id', $contestId)
            ->orderBy('answer_timing')
            ->get()
            ->groupBy(['contest_id']);
        $data = [];
        foreach ($answers as $contestId => $contestQuestions) {
            $totalQuestions = count($contestQuestions);
            $totalQuestionInContest = ContestQuestion::where('contest_id',$contestId)->count();
            $trueQuestions = 0;
            foreach ($contestQuestions as $userId => $userQuestions) {
                $majorityQuestionAnswer = CorrectAnswer::where('contest_id',
                    $userQuestions->contest_id)
                    ->where('question_id', $userQuestions->question_id)
                    ->orderBy('answer_count','desc')
                    ->first();
                if ($majorityQuestionAnswer->option_id == $userQuestions->option_id ?? 0) {
                    $trueQuestions += 1;
                }

                $wrongQuestions = $totalQuestions   - $trueQuestions; 
                $totalPercentage = $trueQuestions * 100 / $totalQuestions;
                $completion = $totalQuestions * 100 / $totalQuestionInContest;
                $totalPercentage = (double) number_format($totalPercentage, 2);
                $completionPercentage = (double) number_format($completion, 2);
                $data = [
                    'contest_name' => $userQuestions->contest->name ?? '',
                    'contest_id' => $userQuestions->contest_id ?? '',
                    'total_questions' => $totalQuestions ?? 0,
                    'correct_questions' => $trueQuestions ?? 0,
                    'wrong_questions' => $wrongQuestions ?? 0,
                    'percentage' => $totalPercentage,
                    'completion' => $completionPercentage."%",
                ];
            }
        }
        
        //if user result data empty 
        if (empty($data)) {
            return $data;
        }
        // for congratulations message
        $answers = Answer::with('contest')
            ->whereHas('contest', function ($query) {
                $query->whereRaw("(CONCAT(contest_date, ' ', end_time) < ?)", [Carbon::now()->format('Y-m-d H:i:s')]);
            })
            ->where('contest_id', $contestId)
            ->orderBy('answer_timing')
            ->get()
            ->groupBy(['user_id']);
        // Get the count of contest users
        $contestUsersCount = $answers->count();
        $contestResultData = [];
        foreach ($answers as $userId => $userQuestions) {
            $contestResultData[$userId] = [];
            $totalQuestions = count($userQuestions);
            $trueQuestions = 0;
            foreach ($userQuestions as $key => $question) {
                $majorityQuestionAnswer = CorrectAnswer::where('contest_id',
                    $question->contest_id)
                    ->where('question_id', $question->question_id)
                    ->orderBy('answer_count','desc')
                    ->first();
                if ($majorityQuestionAnswer->option_id == $question->option_id ?? 0) {
                    $trueQuestions += 1;
                }
                
            }
            $totalPercentage = $trueQuestions * 100 / $totalQuestions;
            $totalPercentage = (double) number_format($totalPercentage, 2);
            $contestResultData[$userId]['percentage'] = $totalPercentage;
            $contestResultData[$userId]['user_id'] = $userId;
        }
        $results = [];

        $prepareRankData = collect($contestResultData)->sortByDesc('percentage');
        $rank = 0;
        foreach ($prepareRankData as $rankData) {
            $rankData['rank'] = ++$rank;
            $results[] = $rankData;
        }
        $userId = Session::get('userId');
        $userResults = collect($results)->where('user_id',$userId)->first();
        //if user rank is 1 to 4
        if ($userResults && ! empty($userResults['rank']) && in_array($userResults['rank'],  config('global.contest_winner_rank_array'))) {
            $data['is_won'] = true;
            $data['total_users'] = 0;
            $data['user_position'] = 0;
            $wordArray = [
                1 => 'First',
                2 => 'Second',
                3 => 'Third',
                4 => 'Fourth',
            ];
            $word = $wordArray[$userResults['rank']] ?? '';
            $data['congratulation_message'] = "<p style='text-align: center'><strong>Congratulations!</strong><br>  You <span style='color: #FFD579'>won </span> $word prize! Our team will reach out to you soon for your reward.</p>";
        } else {
          $outOfNumber =  $this->getCongratulationsMessage($contestUsersCount, $userResults['rank'] ?? 0);
            $data['is_won'] = false;
            $data['total_users'] = $contestUsersCount;
            $data['user_position'] = $outOfNumber;
            $outOfNumber =  $this->getCongratulationsMessage($contestUsersCount, $userResults['rank'] ?? 0);
            $data['congratulation_message'] = "<p style='text-align: center'><strong>Congratulations!</strong><br> 
 You’re in the <span style='color: #FFD579'>Top $outOfNumber</span> out of .$contestUsersCount. participants.</p>";
        }
        
        return $data;
    }

    public function getCongratulationsMessage($contestUsersCount,$userRank)
    {
        $threshold = config('global.low_threshold');
        if ($contestUsersCount < 10) {
            $threshold = config('global.low_threshold');
        } elseif ($contestUsersCount >= 10 && $contestUsersCount < 100) {
            $threshold = config('global.mid_threshold');
        } elseif ($contestUsersCount >= 100 && $contestUsersCount < 1000) {
            $threshold = config('global.hig_threshold');
        } elseif ($contestUsersCount >= 1000 && $contestUsersCount < 10000) {
            $threshold = config('global.threshold');
        } elseif ($contestUsersCount >= 10000) {
            $threshold = config('global.above_threshold');
        }

        $rankingCount =  ceil($userRank / $threshold) * $threshold <= $contestUsersCount ?  ceil($userRank / $threshold) * $threshold : $contestUsersCount;
        

        return intval($rankingCount);
    }



    /**
     * @param $request
     *
     * @return array
     */
    public function contestPlayedAnswerData($request)
    {
        
        $userId = Session::get('userId');
        $contestId = $request['contest_id'];
        $answers = Answer::where('user_id', $userId)
            ->where('contest_id', $contestId)
            ->whereHas('contest', function ($query) {
                $query->whereRaw("(CONCAT(contest_date, ' ', end_time) < ?)", [Carbon::now()->format('Y-m-d H:i:s')]);
            })
            ->get()
            ->groupBy(['contest_id']);
        $data = [];
        foreach ($answers as $contestId => $contestQuestions) {
            foreach ($contestQuestions as $key => $userQuestions) {
                $correctQuestionId = 0;
                $isTrueQuestion = false;
                $majorityQuestionAnswer = CorrectAnswer::where('contest_id',
                    $userQuestions->contest_id)
                    ->where('question_id', $userQuestions->question_id)
                    ->orderBy('answer_count','desc')
                    ->first();
                if ($majorityQuestionAnswer->option_id == $userQuestions->option_id ?? 0) {
                    $correctQuestionId = $userQuestions->option_id;
                    $isTrueQuestion = true;
                }
                $questionOptions = $userQuestions->question->option ?? [];
                $options = [];
                $answersForCount = Answer::where('contest_id', $contestId)
                    ->where('question_id', $userQuestions->question_id)
                    ->get();

                $userCount = $answersForCount->groupBy('user_id')->count();
                foreach ($questionOptions as $questionOption) {
                    $optionAnswerCount = $answersForCount->where('option_id', $questionOption->id)->count();
                    $userAnswerTimeSum = $answersForCount->where('option_id', $questionOption->id)->sum('answer_timing');
                    $percentage = $userCount > 0 ? (double) number_format($optionAnswerCount / $userCount * 100, 2) : 0;

                    $options[] = [
                        'id' => $questionOption->id,
                        'option' => $questionOption->option ?? '',
                        'percentage' => $percentage,
                        'answer_timing' => $userAnswerTimeSum,
                        'is_majority_answer' => $questionOption->id == $majorityQuestionAnswer->option_id
                    ];
                }

                $data[$key] = [
                    'question' => $userQuestions->question->question ?? '',
                    'option'   => $options ?? [],
                    'is_question_correct'  => $isTrueQuestion,
                    'selected_option_id'  => $userQuestions->option_id,
                ];
                
                $optionName = QuestionOption::where('id',$majorityQuestionAnswer->option_id)->first()->option ?? '';
                if ($isTrueQuestion){
                    $data[$key]['message'] = "<p>Great! Most of the participants chose <strong>" . $optionName . "</strong></p>";
                }else{
                    $data[$key]['message'] = "<p>Oops! Most of the participants chose <strong>".$optionName. "</strong></p>";
                }
            }
        }

        $data = collect($data)->where('is_question_correct',$request['answer_type'] ?? '')->toArray();
        
        return  $data;
    }
}
