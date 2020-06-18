<?php

namespace Domains\Roles\Http\Requests;

use Domains\Roles\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Role::class);
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
                Rule::in(Role::getValidScopesFor($this->user()->account_type)),
            ],
        ];
    }
}
