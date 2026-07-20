<?php

namespace App\Http\Requests\Administration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => strtolower(
                trim((string) $this->input('name'))
            ),
            'permissions' => $this->input(
                'permissions',
                []
            ),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')
                    ->where(
                        fn ($query) =>
                        $query->where(
                            'guard_name',
                            'web'
                        )
                    ),
            ],

            'permissions' => [
                'nullable',
                'array',
            ],

            'permissions.*' => [
                'integer',
                'exists:permissions,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' =>
                'Role name is required.',

            'name.unique' =>
                'This role already exists.',

            'permissions.array' =>
                'Permissions must be provided as a list.',

            'permissions.*.exists' =>
                'One of the selected permissions does not exist.',
        ];
    }
}
