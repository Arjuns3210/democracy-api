<?php

namespace App\Repositories\webservices;

//Common
use Illuminate\Http\Request;
use App\Models\Contest;
use App\Models\EnrolledContest;
use Carbon\Carbon;

/**
 * Class ContestRepository
 *
 * @package App\Repositories\webservices
 * @version Jan 19, 2024
 */
class ContestRepository
{
    /**
     * Get Contest Data
     *
     * @param array $attributes
     * @return null
     */

    public function getByRequest(array $attributes)
    {
        $contest = Contest::where('status', '1')->whereHas('questions');

        if (isset($attributes['contest_type'])) {

            $nowDay = Carbon::now()->format('Y-m-d');
            $nowTime = Carbon::now()->format('H:i:s');

            if ($attributes['contest_type'] == 'featured_contest') {
                $targetDateTime = Carbon::now()->addDays(7);
                $contest =  $contest->where('registration_start_date', '<=', $nowDay)
                    ->where('contest_date', '>', $targetDateTime->format('Y-m-d'));
            }
            
            if ($attributes['contest_type'] == 'win_big_this_week') {
                $targetDateTime = Carbon::now()->addDays(7);
                $nowDateTime = Carbon::now()->format('Y-m-d H:i:s');
                $contest =  $contest->where('registration_start_date', '<=', $nowDay)
                    ->whereRaw("(CONCAT(contest_date, ' ', start_time) >= ?)", [$nowDateTime])
                    ->whereRaw("(CONCAT(contest_date, ' ', start_time) <= ?)", [$targetDateTime->format('Y-m-d H:i:s')]);
            }
            
            if ($attributes['contest_type'] == 'live_contest') {
                $minEnrolledContestCount = config('global.enrolled_contests_count');
                $contest = $contest->has('enrolledContests','>=',$minEnrolledContestCount)
                    ->where('contest_date', $nowDay)
                    ->where('start_time', '<=', $nowTime)
                    ->where('end_time', '>=', $nowTime);
            }
        }

        $data['total_count'] = $contest->count();
        if (isset($attributes['paginate'])) {
            $data['result'] = $contest->paginate($attributes['paginate']);
        } else {
            $data['result'] = $contest->get();
        }
        
        return $data;
    }

    /**
     * Get Specified Contest Data
     *
     * @param  int  $contest_id
     * @return null
     */
    public function findWithoutFail($contest_id)
    {
        try {
            $contest = Contest::find($contest_id);
            return $contest;
        } catch (\Exception $e) {
            return null;
        }
    }


    /**
     * check user enrolled contest or not
     *
     * @param  int  $contest_id, $user_id
     * @return null
     */
    public function checkIsEnrolled($contest_id, $user_id)
    {
        try {
            $is_enrolled = EnrolledContest::where('contest_id', $contest_id)->where('user_id', $user_id)->get();
            if (count($is_enrolled) == 0) {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            return null;
        }
    }




}
