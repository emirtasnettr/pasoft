<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInsurancePolicyRequest extends FormRequest
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
            'customer_id' => ['required', 'exists:customers,id'],
            'policy_type_id' => ['required', 'exists:policy_types,id'],
            'insurance_company_id' => ['required', 'exists:insurance_companies,id'],
            'policy_number' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'premium_amount' => ['required', 'numeric', 'min:0'],
            'commission_amount' => ['nullable', 'numeric', 'min:0'],
            'commission_collected' => ['nullable', 'numeric', 'min:0'],
            'premium_payment_status' => ['required', Rule::in(['pending', 'partial', 'collected'])],
            'coverage_details' => ['nullable', 'string', 'max:20000'],
            'renewal_status' => ['required', Rule::in(['active', 'pending_renewal', 'renewed', 'not_renewed'])],
            'renewal_pipeline_stage' => ['nullable', 'string', 'max:64'],
            'renewal_reminder_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'owner_user_id' => ['nullable', 'exists:users,id'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }
}
