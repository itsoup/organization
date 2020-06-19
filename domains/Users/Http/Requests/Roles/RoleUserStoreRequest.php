<?php

namespace Domains\Users\Http\Requests\Roles;

use Domains\Roles\Models\Role;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleUserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->tokenCan('organization:users:view')
            && $this->user()->tokenCan('organization:users:manage')
            && $this->user()->id !== (int) $this->route('userId');
    }

    public function rules(): array
    {
        return [
            'roles' => [
                'required',
                'array',
            ],
            'roles.*' => [
                'required',
                'integer',
                Rule::exists(Role::class, 'id')->where(function (Builder $query) {
                    $query->where('customer_id', $this->user()->customer_id);
                }),
            ],
        ];
    }
}
