<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCollectionRequest extends FormRequest
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

            'payment_schedule_id' => [
                'nullable',
                'exists:payment_schedules,id',
            ],

            'official_receipt_no' => [
                'nullable',
                'string',
                'max:100',
                'unique:collections,official_receipt_no',
            ],

            'payment_date' => [
                'required',
                'date',
            ],

            'amount_paid' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'payment_method' => [
                'required',
                'in:cash,check,bank_transfer,gcash,maya,other',
            ],

            'reference_no' => [
                'nullable',
                'string',
                'max:100',
            ],

            'remarks' => [
                'nullable',
                'string',
            ],
        ];
    }
}
