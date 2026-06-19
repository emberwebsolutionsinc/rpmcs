<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $agentId = $this->route('agent')?->id ?? $this->route('agent');

        return [

            'parent_agent_id' => [
                'nullable',
                'exists:agents,id',
            ],

            'agent_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('agents', 'agent_code')->ignore($agentId),
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

            'contact_number' => [
                'nullable',
                'string',
                'max:50',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
            ],

            'license_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'default_commission_rate' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'agent_type' => [
                'required',
                'in:main_agent,sub_agent,independent_agent',
            ],

            'status' => [
                'nullable',
                'in:active,inactive',
            ],
        ];
    }
}
