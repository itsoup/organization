<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class CustomerStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Customer::class);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
            ],
            'vat_number' => [
                'required',
                'string',
                'unique:customers',
            ],
            'country' => [
                'required',
                'string',
                'size:2',
            ],
        ];
    }
}
