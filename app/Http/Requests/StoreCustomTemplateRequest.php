<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreCustomTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $configuration = $this->input('configuration');
        if (is_string($configuration)) {
            $decoded = json_decode($configuration, true);
            if (! is_array($decoded)) {
                throw ValidationException::withMessages(['configuration' => 'La configuration envoyée est invalide.']);
            }
            $this->merge(['configuration' => $decoded]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'configuration' => ['required', 'array'],
            'configuration.background' => ['nullable', 'regex:/^#[0-9a-fA-F]{3,6}$/'],
            'configuration.elements' => ['required', 'array', 'min:1', 'max:30'],
            'configuration.elements.*.type' => ['required', Rule::in(['shape', 'background', 'text', 'image', 'logo', 'qr_code'])],
            'configuration.elements.*.field' => ['nullable', Rule::in(['full_name', 'job_title', 'company', 'email', 'phone', 'identifier', 'photo', 'logo'])],
            'configuration.elements.*.x' => ['required', 'integer', 'between:0,2000'],
            'configuration.elements.*.y' => ['required', 'integer', 'between:0,2000'],
            'configuration.elements.*.width' => ['required', 'integer', 'between:10,2000'],
            'configuration.elements.*.height' => ['required', 'integer', 'between:10,2000'],
            'configuration.elements.*.color' => ['nullable', 'regex:/^#[0-9a-fA-F]{3,6}$/'],
            'configuration.elements.*.font_size' => ['nullable', 'integer', 'between:8,120'],
            'configuration.elements.*.content' => ['nullable', 'string', 'max:120'],
            'configuration.elements.*.z_index' => ['nullable', 'integer', 'between:0,99'],
        ];
    }
}
