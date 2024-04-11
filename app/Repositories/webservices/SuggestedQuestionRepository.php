<?php

namespace App\Repositories\webservices;

//Common
use Carbon\Carbon;
use App\Models\SuggestedQuestion;
use App\Models\SuggestedQuestionOption;
use Illuminate\Support\Facades\DB;
use Session;
use Log;

/**
 * Class SuggestedQuestionRepository
 *
 * @package App\Repositories\webservices
 * @version Jan 15, 2024
 */
class SuggestedQuestionRepository
{

    /**
     * @param $id
     * @param array $columns
     * @return mixed|null
     */
    public function findWithoutFail($id)
    {
        try {
            $suggestedQuestion = SuggestedQuestion::find($id);
            return $suggestedQuestion;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Update User
     *
     * @param array $attributes
     */
    public function create(array $attributes)
    {
        try {
            DB::beginTransaction();

            $question = $attributes['question'];
            $options = $attributes['option'];

            $suggestedQuestion = SuggestedQuestion::create([
                'user_id' => Session::get('userId'),
                'question' => $question,
                'created_by' => Session::get('userId')
            ]);

            $optionsData = [];
            foreach ($options as $option) {
                SuggestedQuestionOption::create([
                    'suggested_question_id' => $suggestedQuestion->id,
                    'option' => $option,
                    'created_by' => Session::get('userId')
                ]);
            }

            DB::commit();

            return $suggestedQuestion;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            throw $e;
        }
    }

}
