<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_code' => ['required', 'string', 'max:50', 'unique:clients,client_code'],
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'suffix' => ['nullable', 'string', 'max:20'],
            'birthdate' => ['nullable', 'date'],
            'civil_status' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'tin' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'contact_number' => ['nullable', 'string', 'max:50'],
            'telephone_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,inactive'],
        ];
    }

     public function messages(): array
    {
        return [
            'first_name.required' =>
                'First name is required.',

            'last_name.required' =>
                'Last name is required.',

            'client_code.unique' =>
                'This client code is already in use.',

            'email.email' =>
                'Enter a valid email address.',

            'birthdate.before_or_equal' =>
                'Birthdate cannot be in the future.',

            'status.in' =>
                'Select a valid client status.',
        ];
    }
}
