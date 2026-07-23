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
        $agent = $this->route('agent');

        $agentId = is_object($agent)
            ? $agent->id
            : $agent;

        return [
            'agent_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('agents', 'agent_code')
                    ->ignore($agentId),
            ],

            'agent_type' => [
                'required',
                Rule::in(['sub_agent']),
            ],

            'parent_agent_id' => [
                'required',
                'integer',
                'exists:agents,id',
                function (
                    string $attribute,
                    mixed $value,
                    \Closure $fail
                ) {
                    $isMainAgent = \App\Models\Agent::query()
                        ->whereKey($value)
                        ->where(
                            'agent_type',
                            'main_agent'
                        )
                        ->exists();

                    if (! $isMainAgent) {
                        $fail(
                            'The selected parent must be a Main Agent.'
                        );
                    }
                },
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
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'default_commission_rate' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'agent_type' => 'sub_agent',

            'middle_name' =>
                $this->filled('middle_name')
                    ? trim($this->middle_name)
                    : null,

            'suffix' =>
                $this->filled('suffix')
                    ? trim($this->suffix)
                    : null,

            'contact_number' =>
                $this->filled('contact_number')
                    ? trim($this->contact_number)
                    : null,

            'email' =>
                $this->filled('email')
                    ? trim($this->email)
                    : null,

            'address' =>
                $this->filled('address')
                    ? trim($this->address)
                    : null,
        ]);
    }
}
