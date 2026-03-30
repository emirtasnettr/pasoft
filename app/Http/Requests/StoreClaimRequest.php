<?php

namespace App\Http\Requests;

use App\Services\ClaimPresenter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClaimRequest extends FormRequest
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
            'policy_id' => ['nullable', 'exists:policies,id'],
            'insurance_company_id' => ['nullable', 'exists:insurance_companies,id'],
            'file_number' => ['required', 'string', 'max:255'],
            'claim_type' => ['nullable', 'string', 'max:128'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(ClaimPresenter::STATUSES)],
            'customer_notice_notes' => ['nullable', 'string', 'max:10000'],
            'internal_notes' => ['nullable', 'string', 'max:10000'],
            'handler_user_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
