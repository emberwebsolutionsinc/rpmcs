<?php

namespace App\Http\Requests\Administration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (
            $this->has(
                'is_active'
            )
        ) {
            $this->merge([
                'is_active' =>
                    filter_var(
                        $this->input(
                            'is_active'
                        ),
                        FILTER_VALIDATE_BOOLEAN,
                        FILTER_NULL_ON_FAILURE
                    ),
            ]);
        }
    }

    public function rules(): array
    {
        $userId =
            $this->route('user')?->id ??
            $this->route('user');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',

                Rule::unique(
                    'users',
                    'email'
                )->ignore($userId),
            ],

            'roles' => [
                'required',
                'array',
                'min:1',
            ],

            'roles.*' => [
                'required',
                'integer',
                'exists:roles,id',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'The user name is required.',

            'email.required' =>
                'The email address is required.',

            'email.email' =>
                'Please enter a valid email address.',

            'email.unique' =>
                'The email address is already in use.',

            'roles.required' =>
                'At least one role is required.',

            'roles.array' =>
                'The selected roles are invalid.',

            'roles.min' =>
                'At least one role must be selected.',

            'roles.*.integer' =>
                'Each selected role must be valid.',

            'roles.*.exists' =>
                'One of the selected roles does not exist.',

            'is_active.required' =>
                'The account status is required.',

            'is_active.boolean' =>
                'The account status must be active or inactive.',
        ];
    }
}
