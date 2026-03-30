<?php

namespace App\Http\Requests;

use App\Models\InsuranceCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInsuranceCompanyRequest extends FormRequest
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
        $company = $this->route('insurance_company');
        $id = $company instanceof InsuranceCompany ? $company->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:64',
                Rule::unique('insurance_companies', 'code')->ignore($id),
            ],
            'logo' => ['nullable', 'image', 'max:2048'],
            'contact_phone' => ['nullable', 'string', 'max:32'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
            'api_enabled' => ['sometimes', 'boolean'],
            'quote_integration_enabled' => ['sometimes', 'boolean'],
        ];
    }
}
