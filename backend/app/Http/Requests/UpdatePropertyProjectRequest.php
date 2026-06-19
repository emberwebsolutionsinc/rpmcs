<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $projectId = $this->route('property_project')?->id ?? $this->route('property_project');

        return [
            'project_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('property_projects', 'project_code')->ignore($projectId),
            ],
            'project_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'developer_name' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'municipality' => ['nullable', 'string', 'max:100'],
            'barangay' => ['nullable', 'string', 'max:100'],
            'street' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:active,inactive'],
        ];
    }
}
