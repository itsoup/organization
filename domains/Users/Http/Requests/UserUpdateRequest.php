<?php

namespace Domains\Users\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->id !== (int) $this->route('userId')
            && (
                $this->user()->isSystemOperator()
                || (
                    $this->user()->isUser() && $this->missing('customer_id')
                )
            );
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
