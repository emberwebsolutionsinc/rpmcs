<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'property_project_id' => ['required', 'exists:property_projects,id'],
            'project_phase_id' => ['nullable', 'exists:project_phases,id'],
            'block_no' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,inactive'],
        ];
    }
}
