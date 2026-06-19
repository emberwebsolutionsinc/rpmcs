<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $reservationId = $this->route('reservation')?->id ?? $this->route('reservation');

        return [
            'reservation_no' => [
                'required',
                'string',
                'max:100',
                Rule::unique('reservations', 'reservation_no')->ignore($reservationId),
            ],

            'client_id' => ['required', 'exists:clients,id'],
            'lot_id' => ['required', 'exists:lots,id'],
            'agent_id' => ['nullable', 'exists:agents,id'],

            'reservation_date' => ['required', 'date'],
            'expiration_date' => ['nullable', 'date', 'after_or_equal:reservation_date'],

            'reservation_fee' => ['required', 'numeric', 'min:0'],

            'status' => ['required', 'in:active,expired,cancelled,converted_to_sale'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
