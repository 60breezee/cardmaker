<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('card')) ?? false;
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:120'], 'data' => ['nullable', 'array'], 'data.*' => ['nullable', 'string', 'max:1000'], 'is_public' => ['sometimes', 'boolean']];
    }
}
