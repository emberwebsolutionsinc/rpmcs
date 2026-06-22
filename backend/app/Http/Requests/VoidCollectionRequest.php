<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoidCollectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'void_reason' => [
                'required',
                'string',
                'min:5',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'void_reason.required' => 'Please provide the reason for voiding this collection.',
            'void_reason.min' => 'The void reason must be at least 5 characters.',
        ];
    }
}
