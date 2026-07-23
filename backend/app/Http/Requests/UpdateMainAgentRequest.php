<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMainAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'max:255',
            ],

            'middle_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'last_name' => [
                'required',
                'string',
                'max:255',
            ],

            'suffix' => [
                'nullable',
                'string',
                'max:50',
            ],

            'contact_number' => [
                'nullable',
                'string',
                'max:50',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'default_commission_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],

            'status' => [
                'required',
                'in:active,inactive',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' =>
                'The first name field is required.',

            'last_name.required' =>
                'The last name field is required.',

            'email.email' =>
                'Please enter a valid email address.',

            'default_commission_rate.numeric' =>
                'The commission rate must be a valid number.',

            'default_commission_rate.min' =>
                'The commission rate cannot be less than 0.',

            'default_commission_rate.max' =>
                'The commission rate cannot be greater than 100.',

            'status.required' =>
                'The status field is required.',

            'status.in' =>
                'The selected status is invalid.',
        ];
    }
}
