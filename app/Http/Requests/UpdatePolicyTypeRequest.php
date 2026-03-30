<?php

namespace App\Http\Requests;

use App\Models\PolicyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePolicyTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $type = $this->route('policy_type');
        $id = $type instanceof PolicyType ? $type->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:64',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('policy_types', 'slug')->ignore($id),
            ],
            'description' => ['nullable', 'string', 'max:10000'],
            'category' => ['nullable', 'string', 'max:64'],
            'color' => ['nullable', 'string', 'max:16', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon' => ['nullable', 'string', 'max:64', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'renewal_reminder_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
