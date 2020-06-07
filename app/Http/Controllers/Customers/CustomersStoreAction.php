<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Http\Requests\CustomerStoreRequest;
use Illuminate\Http\Response;

class CustomersStoreAction extends Controller
{
    private Customer $customers;

    public function __construct(Customer $customers)
    {
        $this->middleware('auth:sanctum');

        $this->customers = $customers;
    }

    public function __invoke(CustomerStoreRequest $request): Response
    {
        $this->customers->create([
            'vat_number' => $request->input('vat_number'),
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'country' => $request->input('country'),
            'logo' => $request->hasFile('logo') ? $request->file('logo')->store('customers') : null,
        ]);

        return Response::create('', Response::HTTP_CREATED);
    }
}
