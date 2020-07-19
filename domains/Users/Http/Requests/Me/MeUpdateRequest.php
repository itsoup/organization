<?php

namespace Domains\Users\Http\Requests\Me;

use Domains\Users\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'filled',
                'string',
            ],
            'email' => [
                'filled',
                'email',
                Rule::unique(User::class)->ignore($this->user()),
            ],
            'vat_number' => [
                'filled',
                Rule::unique(User::class)->ignore($this->user()),
            ],
            'password' => [
                'filled',
                'confirmed',
            ],
        ];
    }
}
