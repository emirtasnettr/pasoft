<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
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
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'type' => ['sometimes', 'required', Rule::in(['call', 'visit', 'proposal', 'follow_up', 'other'])],
            'due_at' => ['nullable', 'date'],
            'remind_at' => ['nullable', 'date'],
            'status' => ['sometimes', 'required', Rule::in(['pending', 'in_progress', 'done', 'cancelled'])],
            'priority' => ['sometimes', 'required', Rule::in(['low', 'medium', 'high'])],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'lead_id' => ['nullable', 'exists:leads,id'],
            'policy_id' => ['nullable', 'exists:policies,id'],
        ];
    }
}
