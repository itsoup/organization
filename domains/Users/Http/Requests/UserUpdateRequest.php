<?php

namespace Domains\Users\Http\Requests;

use Domains\Users\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()
            ->can('update', [
                User::class,
                $this->route('userId'),
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
                'filled',
                'email',
                'unique:users',
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
