<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'source' => ['nullable', 'string', 'max:128'],
            'stage' => ['required', Rule::in(['new', 'contacted', 'proposal', 'meeting', 'won', 'lost'])],
            'estimated_value' => ['nullable', 'numeric', 'min:0'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'next_follow_up_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
