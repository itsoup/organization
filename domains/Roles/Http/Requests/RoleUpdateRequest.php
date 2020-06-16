<?php

namespace Domains\Roles\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
            ],
            'scopes' => [
                'required',
                'array',
            ],
            'scopes.*' => [
                'required',
                'string',
            ],
        ];
    }
}
