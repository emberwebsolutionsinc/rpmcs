<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reservation_no' => ['required', 'string', 'max:100', 'unique:reservations,reservation_no'],

            'client_id' => ['required', 'exists:clients,id'],
            'lot_id' => ['required', 'exists:lots,id'],
            'agent_id' => ['nullable', 'exists:agents,id'],

            'reservation_date' => ['required', 'date'],
            'expiration_date' => ['nullable', 'date', 'after_or_equal:reservation_date'],

            'reservation_fee' => ['required', 'numeric', 'min:0'],

            'status' => ['nullable', 'in:active,expired,cancelled,converted_to_sale'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
