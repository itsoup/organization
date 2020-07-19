<?php

namespace Domains\Users\Http\Requests\Users;

use Domains\Users\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()
            ->can('create', [
                User::class,
                $this->missing('customer_id'),
            ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
            ],
            'email' => [
                'required',
                'email',
                'unique:users',
            ],
            'password' => [
                'required',
                'min:6',
            ],
            'customer_id' => [
                'sometimes',
                'nullable',
                'exists:customers,id',
            ],
            'vat_number' => [
                'sometimes',
                'unique:users',
            ],
        ];
    }
}
