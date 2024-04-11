<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class SuggestQuestionRequest extends FormRequest
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
            'question' => 'required|string|max:200',
            'option' => 'required|array|max:7|min:2',
            'option.*' => 'required|string|max:200',
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

     /**
     * Get custom error messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'question.required' => 'The question is required.',
            'question.string' => 'The question must be a string.',
            'question.max' => 'The question must not exceed 200 characters.',
            'option.required' => 'The options are required.',
            'option.max' => 'The option may not have more than 7 items.',
            'option.*.required' => 'Each option is required.',
            'option.*.string' => 'Each option must be a string.',
            'option.*.max' => 'Each option must not exceed 200 characters.',
        ];
    }
}
