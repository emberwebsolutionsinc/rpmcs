<?php

namespace App\Http\Requests\Administration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateRoleRequest extends FormRequest
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
        $role = $this->route('role');

        $roleId = $role instanceof Role
            ? $role->id
            : $role;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')
                    ->ignore($roleId)
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

            'permissions.*.exists' =>
                'One of the selected permissions does not exist.',
        ];
    }
}
