<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $saleId =
            $this->route('sale')?->id
            ?? $this->route('sale');

        return [

            'sale_no' => [
                'required',
                'string',
                'max:100',
                Rule::unique(
                    'sales',
                    'sale_no'
                )->ignore($saleId),
            ],

            'reservation_id' => [
                'nullable',
                'exists:reservations,id',
            ],

            'client_id' => [
                'required',
                'exists:clients,id',
            ],

            'lot_id' => [
                'required',
                'exists:lots,id',
            ],

            'agent_id' => [
                'nullable',
                'exists:agents,id',
            ],

            'sale_date' => [
                'required',
                'date',
            ],

            'contract_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'downpayment' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'balance' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'status' => [
                'required',
                'in:active,cancelled,fully_paid',
            ],

            'remarks' => [
                'nullable',
                'string',
            ],
        ];
    }
}
