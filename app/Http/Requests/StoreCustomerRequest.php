<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
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
            'type' => ['required', Rule::in(['individual', 'corporate'])],
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255', 'required_if:type,corporate'],
            'national_id' => ['nullable', 'string', 'max:32'],
            'tax_number' => ['nullable', 'string', 'max:32'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'segment' => ['required', Rule::in(['vip', 'active', 'passive', 'potential'])],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
