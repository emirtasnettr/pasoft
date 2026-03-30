<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('system_admin') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'site_title' => ['nullable', 'string', 'max:120'],
            'site_description' => ['nullable', 'string', 'max:1024'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'favicon' => ['nullable', 'image', 'max:1024'],
            'hero_screenshot' => [
                'nullable',
                'image',
                'max:5120',
                'dimensions:max_width=3840,max_height=2160',
            ],
            'remove_logo' => ['sometimes', 'boolean'],
            'remove_favicon' => ['sometimes', 'boolean'],
            'remove_hero_screenshot' => ['sometimes', 'boolean'],
            'pricing_plans' => ['nullable', 'array', 'size:4'],
            'pricing_plans.*.period' => ['required', 'string', 'max:80'],
            'pricing_plans.*.price_label' => ['nullable', 'string', 'max:120'],
            'pricing_plans.*.highlight' => ['boolean'],
            'pricing_plans.*.badge' => ['nullable', 'string', 'max:40'],
        ];
    }
}
