<?php

namespace App\Http\Requests;

use App\Services\ClaimPresenter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClaimStatusRequest extends FormRequest
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
            'status' => ['required', Rule::in(ClaimPresenter::STATUSES)],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
