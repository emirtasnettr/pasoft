<?php

namespace App\Http\Requests;

use App\Models\Installment;
use App\Models\InsurancePolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePaymentRequest extends FormRequest
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
            'installment_id' => ['nullable', 'exists:installments,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', Rule::in(['cash', 'card', 'transfer'])],
            'paid_at' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:128'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $customerId = (int) $this->input('customer_id');
            if ($customerId <= 0) {
                return;
            }

            $policyId = $this->filled('policy_id') ? (int) $this->input('policy_id') : null;
            $installmentId = $this->filled('installment_id') ? (int) $this->input('installment_id') : null;

            if ($policyId !== null) {
                $policy = InsurancePolicy::query()->find($policyId);
                if ($policy === null || (int) $policy->customer_id !== $customerId) {
                    $v->errors()->add('policy_id', 'Seçilen poliçe bu müşteriye ait değil.');
                }
            }

            if ($installmentId !== null) {
                $installment = Installment::query()->with('policy')->find($installmentId);
                if ($installment === null || $installment->policy === null) {
                    $v->errors()->add('installment_id', 'Geçersiz taksit kaydı.');

                    return;
                }
                if ((int) $installment->policy->customer_id !== $customerId) {
                    $v->errors()->add('installment_id', 'Bu taksit seçilen müşteriye ait değil.');
                }
                if ($policyId !== null && (int) $installment->policy_id !== $policyId) {
                    $v->errors()->add('installment_id', 'Taksit, seçilen poliçe ile eşleşmiyor.');
                }
            }
        });
    }
}
