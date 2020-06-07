<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() === null;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'exists:users',
            ],
            'password' => [
                'required',
                'min:6',
            ],
            'client_name' => [
                'required',
                'string',
            ],
        ];
    }
}
