<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class CreateContestAnswerApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'contest_id' => 'required|integer|exists:contests,id',
            'question_id' => 'required|array',
            'question_id.*' => 'required|integer',
            'option_id' => 'required|array',
            'option_id.*' => 'required|integer',
            'answer_timing'=>'required|integer'
        ];
    }
    public function messages()
    {
        return [
            'question_id.*.required' => 'Each question id is required.',
            'question_id.*.integer' => 'Each question id must be an integer.',
            'option_id.*.required' => 'Each option id is required.',
            'option_id.*.integer' => 'Each option id must be an integer.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'success' => 0,
            'message' => $validator->errors()->all()
        ]);

        throw (new ValidationException($validator, $response))
            ->errorBag($this->errorBag)
            ->redirectTo($this->getRedirectUrl());
    }
}
