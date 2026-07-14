<?php

namespace App\Http\Requests;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /**
         * Because the route uses:
         *
         * /client-management/clients/{client}
         *
         * Laravel route model binding normally gives us
         * an actual Client model here.
         */
        $client = $this->route('client');

        $clientId = $client instanceof Client
            ? $client->id
            : $client;

        return [
            'client_code' => [
                'nullable',
                'string',
                'max:50',

                /**
                 * Client code must remain unique,
                 * except for the client currently being edited.
                 */
                Rule::unique('clients', 'client_code')
                    ->ignore($clientId),
            ],

            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'middle_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'suffix' => [
                'nullable',
                'string',
                'max:20',
            ],

            'birthdate' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'gender' => [
                'nullable',
                Rule::in([
                    'male',
                    'female',
                    'other',
                ]),
            ],

            'civil_status' => [
                'nullable',
                Rule::in([
                    'single',
                    'married',
                    'widowed',
                    'separated',
                    'divorced',
                ]),
            ],

            'nationality' => [
                'nullable',
                'string',
                'max:100',
            ],

            'contact_number' => [
                'nullable',
                'string',
                'max:50',
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'occupation' => [
                'nullable',
                'string',
                'max:150',
            ],

            'employer' => [
                'nullable',
                'string',
                'max:150',
            ],

            'tin' => [
                'nullable',
                'string',
                'max:50',
            ],

            'status' => [
                'required',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'client_code.unique' =>
                'This client code is already assigned to another client.',

            'client_code.max' =>
                'The client code must not exceed 50 characters.',

            'first_name.required' =>
                'First name is required.',

            'first_name.max' =>
                'First name must not exceed 100 characters.',

            'last_name.required' =>
                'Last name is required.',

            'last_name.max' =>
                'Last name must not exceed 100 characters.',

            'birthdate.date' =>
                'Enter a valid birthdate.',

            'birthdate.before_or_equal' =>
                'Birthdate cannot be later than today.',

            'gender.in' =>
                'Select a valid gender.',

            'civil_status.in' =>
                'Select a valid civil status.',

            'email.email' =>
                'Enter a valid email address.',

            'status.required' =>
                'Client status is required.',

            'status.in' =>
                'Select a valid client status.',
        ];
    }

    /**
     * Convert empty string values from the Vue form to null
     * before validation and saving.
     */
    protected function prepareForValidation(): void
    {
        $nullableFields = [
            'client_code',
            'middle_name',
            'suffix',
            'birthdate',
            'gender',
            'civil_status',
            'nationality',
            'contact_number',
            'email',
            'address',
            'occupation',
            'employer',
            'tin',
        ];

        $prepared = [];

        foreach ($nullableFields as $field) {
            if ($this->has($field)) {
                $value = $this->input($field);

                $prepared[$field] =
                    is_string($value) && trim($value) === ''
                        ? null
                        : $value;
            }
        }

        $this->merge($prepared);
    }
}
