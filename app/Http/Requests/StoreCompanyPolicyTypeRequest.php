<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyPolicyTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('min_commission') === '') {
            $this->merge(['min_commission' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'insurance_company_id' => [
                'required',
                'exists:insurance_companies,id',
                Rule::unique('company_policy_type', 'insurance_company_id')->where(
                    fn ($q) => $q->where('policy_type_id', (int) $this->input('policy_type_id')),
                ),
            ],
            'policy_type_id' => ['required', 'exists:policy_types,id'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'min_commission' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
