<?php

namespace App\Repositories\webservices;

//Common
use App\Models\Answer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class ContestAnswerRepository
 *
 * @package App\Repositories\webservices
 * @version Sep 23, 2023
 */
class ContestAnswerRepository
{
    /**
     * Create Contest answer
     *
     * @param array $attributes
     */
    public function create(array $attributes)
    {
        try {
            DB::beginTransaction();
            $answer = Answer::create($attributes);
            DB::commit();
            return $answer;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            throw $e;
        }
    }

}
