<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMainAgentRequest extends FormRequest
{
    /**
     * Determine whether the authenticated user
     * is authorized to create a main agent.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a main agent.
     */
    public function rules(): array
    {
        return [
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

            'contact_number' => [
                'nullable',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('agents', 'email'),
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
                'nullable',
                Rule::in([
                    'active',
                    'inactive',
                ]),
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'first_name.required' =>
                'The first name is required.',

            'first_name.string' =>
                'The first name must be valid text.',

            'first_name.max' =>
                'The first name must not exceed 100 characters.',

            'middle_name.string' =>
                'The middle name must be valid text.',

            'middle_name.max' =>
                'The middle name must not exceed 100 characters.',

            'last_name.required' =>
                'The last name is required.',

            'last_name.string' =>
                'The last name must be valid text.',

            'last_name.max' =>
                'The last name must not exceed 100 characters.',

            'suffix.string' =>
                'The suffix must be valid text.',

            'suffix.max' =>
                'The suffix must not exceed 20 characters.',

            'contact_number.string' =>
                'The contact number must be valid text.',

            'contact_number.max' =>
                'The contact number must not exceed 30 characters.',

            'email.email' =>
                'Please enter a valid email address.',

            'email.max' =>
                'The email address must not exceed 255 characters.',

            'email.unique' =>
                'The email address is already used by another agent.',

            'address.string' =>
                'The address must be valid text.',

            'address.max' =>
                'The address must not exceed 1000 characters.',

            'default_commission_rate.numeric' =>
                'The commission rate must be a valid number.',

            'default_commission_rate.min' =>
                'The commission rate cannot be lower than zero.',

            'default_commission_rate.max' =>
                'The commission rate cannot exceed 100 percent.',

            'status.in' =>
                'The selected agent status is invalid.',
        ];
    }

    /**
     * Prepare and normalize the submitted values
     * before Laravel runs the validation rules.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => $this->normalizeString(
                $this->input('first_name')
            ),

            'middle_name' => $this->normalizeString(
                $this->input('middle_name')
            ),

            'last_name' => $this->normalizeString(
                $this->input('last_name')
            ),

            'suffix' => $this->normalizeString(
                $this->input('suffix')
            ),

            'contact_number' => $this->normalizeString(
                $this->input('contact_number')
            ),

            'email' => $this->normalizeEmail(
                $this->input('email')
            ),

            'address' => $this->normalizeString(
                $this->input('address')
            ),

            'default_commission_rate' => $this->filled(
                'default_commission_rate'
            )
                ? $this->input('default_commission_rate')
                : 0,

            'status' => $this->filled('status')
                ? strtolower(trim((string) $this->input('status')))
                : 'active',
        ]);
    }

    /**
     * Convert an empty string into null and
     * trim non-empty string values.
     */
    private function normalizeString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === ''
            ? null
            : $value;
    }

    /**
     * Normalize email values before validation.
     */
    private function normalizeEmail(mixed $value): ?string
    {
        $email = $this->normalizeString($value);

        return $email
            ? strtolower($email)
            : null;
    }
}
