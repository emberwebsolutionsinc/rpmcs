<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $lotId = $this->route('lot')?->id ?? $this->route('lot');

        return [
            'property_project_id' => ['required', 'exists:property_projects,id'],
            'project_phase_id' => ['nullable', 'exists:project_phases,id'],
            'project_block_id' => ['nullable', 'exists:project_blocks,id'],
            'lot_code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('lots', 'lot_code')->ignore($lotId),
            ],
            'lot_no' => ['required', 'string', 'max:100'],
            'title_no' => ['nullable', 'string', 'max:255'],
            'lot_area' => ['required', 'numeric', 'min:0'],
            'price_per_sqm' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'corner_lot' => ['nullable', 'boolean'],
            'road_lot' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:available,reserved,sold,fully_paid,cancelled'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}
