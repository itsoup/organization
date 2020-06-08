<?php

namespace Domains\Customers\Http\Requests;

use Domains\Customers\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class CustomerUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', Customer::class);
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
                'unique:customers,vat_number,' . $this->route('customerId'),
            ],
            'country' => [
                'required',
                'string',
                'size:2',
            ],
        ];
    }
}
