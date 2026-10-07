<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return ['template_id' => ['required', Rule::exists('templates', 'id')->where('is_active', true)], 'name' => ['required', 'string', 'max:120'], 'data' => ['nullable', 'array'], 'data.*' => ['nullable', 'string', 'max:1000']];
    }
}
