<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GeneratePaymentScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sale_id' => [
                'required',
                'exists:sales,id',
            ],

            'months' => [
                'required',
                'integer',
                'min:1',
                'max:360',
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'overwrite' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}
