<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyPolicyTypeRequest extends FormRequest
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
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'min_commission' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
