<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StorePolicyDocumentRequest extends FormRequest
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
            'file' => ['required', File::types(config('upload.document_extensions'))->max(config('upload.document_max_kilobytes'))],
            'category' => ['nullable', 'string', 'max:64'],
        ];
    }
}
