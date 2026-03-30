<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePolicyRenewalRequest extends FormRequest
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
            'renewal_status' => ['required', Rule::in(['active', 'pending_renewal', 'renewed', 'not_renewed'])],
            'renewal_pipeline_stage' => ['nullable', 'string', 'max:128'],
        ];
    }
}
