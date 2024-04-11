<?php

namespace App\Repositories\webservices;

use App\Models\Contest;
use App\Models\EnrolledContest;
use App\Utils\SearchScopeUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

/**
 * Class EnrolledContestRepository
 *
 * @package App\Repositories\webservices
 * @version Jan 18, 2024
 */
class EnrolledContestRepository
{
    public function getByRequest(array $attributes)
    {
        $userId = Session::get('userId');
        $enrolledContest = EnrolledContest::with('contest')->where('user_id',$userId)->orderByDesc('created_at')->get();
        $enrolledContest->append('contest_details');
        if (!empty($attributes['filter'])){
            $filter = $attributes['filter'];
            if ($filter == 'Completed'){
                $enrolledContest = $enrolledContest->filter(function ($enrolledContest){
                    return is_numeric($enrolledContest->contest_details) || $enrolledContest->contest_details == "Won" ;
                });

            }else{
                $enrolledContest = $enrolledContest->filter(function ($enrolledContest) use ($filter) {
                    return $enrolledContest->contest_details === $filter;
                });
            }
        }
        
        $enrolledContestCollection = collect($enrolledContest->toArray());
        $data['total_count'] = $enrolledContestCollection->count();
        if (!empty($attributes['page']) && !empty($attributes['paginate']) && !empty($data['total_count'])) {
            $page = $attributes['page'];
            $paginate = $attributes['paginate'];
            $startIndex = ($page - 1) * $paginate;
            $itemsPerPage = $paginate;
            $paginatedResults = $enrolledContestCollection->slice($startIndex, $itemsPerPage);
            
            $remainingCount = max(0, $data['total_count'] - $startIndex - $itemsPerPage);
            $data['remaining_count'] = $remainingCount;
            $data['result'] = $paginatedResults->values()->all();
        } else {
            $data['remaining_count'] = 0;
            $data['result'] = $enrolledContestCollection->all();
        }

        return $data;
    }

    public function create(array $attributes)
    {
        try {
            \DB::beginTransaction();
            $success = EnrolledContest::Create($attributes);
            \DB::commit();
            return $success;
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($e);
            throw $e;
        }
    }

}
