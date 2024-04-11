<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Answer;
use App\Models\Contest;
use App\Models\EnrolledContest;
use Illuminate\Support\Arr;
use Session;

class ShowContestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $ruleDetails = explode("\n",$this->rules ?? '');
        $moreContests = $this->getMoreContests();
        $isCancelled = $this->isCancelled();
        $isLive = $this->isLive();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sub_title' => $this->sub_title,
            'contest_details' => $this->contest_details,
            'winning_award' => $this->winning_award,
            'rules' => array_filter($ruleDetails) ?? [],
            'contest_date' => Carbon::parse($this->contest_date)->format('jS M y'),
            'start_time' => Carbon::parse($this->start_time)->format('h:i A'),
            'end_time' => Carbon::parse($this->end_time)->format('h:i A'),
            'duration' => $this->duration,
            'question_count' => $this->question_count,
            'is_enrolled' => $this->is_enrolled,
            'is_played' =>  Answer::where('user_id', Session::get('userId'))->where('contest_id', $this->id)->exists() ?? false,
            'is_live' => $isLive,
            'is_cancelled' => $isCancelled,
            'cancelled_contest_message' =>$isCancelled == true ? config('global.cancelled_contest_message') : '',
            'contest_category_id' => $isLive,
            'get_user_message' => trans('auth.enrolling_without_login'),
            'more_contests' => $isLive ? [] : $moreContests['contests'] ?? [],
            'view_all' => $isLive ? false : $moreContests['view_all'] ?? [],
            'more_contests_type' => $this->getMoreContestsType(),
            'is_terminated' => $this->isTerminated()

        ];
    }
    private function isCancelled()
    {
        $minEnrolledContestCount = config('global.enrolled_contests_count', 5);
        $enrolledCount = EnrolledContest::where('contest_id',$this->id)->count();
        if ( $this->isLive() && $enrolledCount < $minEnrolledContestCount) {
            return true;
        }
        return false;
    }
    private function isLive()
    {
        $nowDay = Carbon::now()->format('Y-m-d');
        $nowTime = Carbon::now()->format('H:i:s');

        if ($this->contest_date == $nowDay &&
            $this->start_time <= $nowTime &&
            $this->end_time >= $nowTime) {
            return true;
        }

        return false;
    }

    private function isTerminated()
    {
        $now = Carbon::now()->format('Y-m-d H:i');
        $contestDateTime = Carbon::parse($this->contest_date . ' ' . $this->end_time)->format('Y-m-d H:i');

        return $now > $contestDateTime;
    }


    public function getMoreContestsType()
    {
        if ($this->isLive()) {

            return 'live_contest';
        }
        $contestDate = Carbon::parse($this->contest_date)->format('Y-m-d');
        $sevenDaysLater = Carbon::now()->addDays(7)->format('Y-m-d');

        if ($contestDate <= $sevenDaysLater) {

            return 'win_big_this_week';
        }

        return 'featured_contest';

    }

    private function getMoreContests()
    {
        $contestType = $this->getMoreContestsType();
        if (!empty($contestType)) {
            $contest = Contest::where('status', '1')->where('id','!=',$this->id)->whereHas('questions');
            $nowDay = Carbon::now()->format('Y-m-d');
            $nowTime = Carbon::now()->format('H:i:s');

            if ($contestType == 'featured_contest') {
                $targetDateTime = Carbon::now()->addDays(7);
                $contest =  $contest->where('registration_start_date', '<=', $nowDay)
                    ->where('contest_date', '>', $targetDateTime->format('Y-m-d'));
            }

            if ($contestType == 'win_big_this_week') {
                $targetDateTime = Carbon::now()->addDays(7);
                $nowDateTime = Carbon::now()->format('Y-m-d H:i:s');
                $contest =  $contest->where('registration_start_date', '<=', $nowDay)
                    ->whereRaw("(CONCAT(contest_date, ' ', start_time) >= ?)", [$nowDateTime])
                    ->whereRaw("(CONCAT(contest_date, ' ', start_time) <= ?)", [$targetDateTime->format('Y-m-d H:i:s')]);
            }

            if ($contestType == 'live_contest') {
                $minEnrolledContestCount = config('global.enrolled_contests_count');
                $contest = $contest->has('enrolledContests','>=',$minEnrolledContestCount)
                    ->where('contest_date', $nowDay)
                    ->where('start_time', '<=', $nowTime)
                    ->where('end_time', '>=', $nowTime);
            }

            $contest = $contest->orderBy('created_at', 'desc')
                ->get();
            $contestCount = $contest->count();
            $viewAll = false;
            $minEnrolledContestCount = config('global.enrolled_contests_count');
            if($contestCount >= $minEnrolledContestCount){
                $viewAll = true;
            }
            $contest=  $contest->take(config('global.contest_details_more_contests_limit', 5));
            $contest->transform(function ($contest) {
                return [
                    'id' => $contest->id,
                    'name' => $contest->name,
                    'sub_title' => $contest->sub_title,
                    'contest_date' => Carbon::parse($contest->contest_date)->format('jS M y'),
                    'start_time' => Carbon::parse($contest->start_time)->format('h:i A'),
                    'question_count' => $this->question_count. " Questions",

                ];
            });
            return [
                'contests'=>$contest,
                'view_all'=> $viewAll,
            ];
        }

        return [
            'contests'=>[],
            'view_all'=> false,
        ];
    }
}
