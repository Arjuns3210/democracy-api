<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use function PHPUnit\Framework\returnSelf;

class EnrolledContest extends Model
{
    use HasFactory;
    protected $fillable = [
        'contest_id',
        'user_id'
    ];

    public $append = ['contest_details'];
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
    public function contest()
    {
        return $this->belongsTo(Contest::class, 'contest_id');
    }

    public function getContestDetailsAttribute()
    {
        $contest = $this->contest;
        $contestStartTime = Carbon::parse($contest->contest_date.' '.$contest->start_time);
        $contestEndTime = Carbon::parse($contest->contest_date.' '.$contest->end_time);
        $currentDateTime = Carbon::now();
        if ($currentDateTime < $contestStartTime) {
            
           return  'Upcoming';
        }

        if ($currentDateTime >= $contestStartTime && $currentDateTime <= $contestEndTime) {
            $userId = Session::get('userId');
            $userAnswerCount = Answer::where('contest_id', $contest->id ?? '')
                ->where('user_id', $userId)
                ->count();
            if($userAnswerCount){
                return "Submitted";
            }
            $minEnrolledContestCount = config('global.enrolled_contests_count', 5);
            $enrolledCount = EnrolledContest::where('contest_id', $contest->id)->count();

            if ($enrolledCount < $minEnrolledContestCount) {
                return "Cancel";
            }

            return "Live";
        }

        // contest finished and get contest result         
        if ($currentDateTime > $contestEndTime) {
            
            $answers = Answer::with('contest')
                ->where('contest_id', $contest->id)
                ->whereHas('contest', function ($query) {
                    $query->whereRaw("(CONCAT(contest_date, ' ', end_time) < ?)", [Carbon::now()->format('Y-m-d H:i:s')]);
                })
                ->orderBy('answer_timing')
                ->get()
                ->groupBy(['user_id']);
           
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
//            if user rank is 1 to 4
            if ($userResults && ! empty($userResults['rank']) && in_array($userResults['rank'], config('global.contest_winner_rank_array'))) {
                
                return "Won";
            }

           if (isset($userResults['percentage'])){
               
             return   $userResults['percentage']; 
           }
        }
        
        return  'Missed';
    }
}
