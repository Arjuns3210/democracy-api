<?php

namespace App\Http\Controllers\webservices;

use App\Http\Controllers\AppBaseController;
use App\Repositories\webservices\SuggestedQuestionRepository;
use App\Http\Requests\SuggestQuestionRequest;
use App\Models\SuggestedQuestionOption;
use App\Models\SuggestedQuestion;
use Session;

class SuggestQuestionApiController extends AppBaseController
{
    /** @var  SuggestedQuestionRepository */
    private $suggestedQuestionRepository;

    public function __construct(SuggestedQuestionRepository $suggestedQuestionRepo)
    {
        $this->suggestedQuestionRepository = $suggestedQuestionRepo;
    }

    /**
     * Suggest Question.
     *
     * @param  SuggestQuestionRequest $request
     * @return \Illuminate\Http\Response
     */
    public function suggestQuestion(SuggestQuestionRequest $request)
    {
        try {
            $input = $request->all();
            $suggestedQuestion = $this->suggestedQuestionRepository->create($input);

            return $this->sendResponse($suggestedQuestion, trans('auth.question_submitted'));
        } catch (\Exception $e) {
            \Log::error("Suggest question failed: " . $e->getMessage());
            return $this->sendError(trans('auth.something_went_wrong'), 500);
        }
    }
}
