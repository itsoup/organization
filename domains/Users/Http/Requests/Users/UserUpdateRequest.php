<?php

namespace Domains\Users\Http\Requests\Users;

use Domains\Users\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()
            ->can('update', [
                User::class,
                (int) $this->route('userId'),
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
                Rule::unique(User::class)->ignore($this->route('userId')),
            ],
            'customer_id' => [
                'sometimes',
                'nullable',
                'exists:customers,id',
            ],
            'vat_number' => [
                'sometimes',
                Rule::unique(User::class)->ignore($this->route('userId')),
            ],
        ];
    }
}
