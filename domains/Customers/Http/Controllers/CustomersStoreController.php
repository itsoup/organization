<?php

namespace Domains\Customers\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Customers\Models\Customer;
use Domains\Customers\Http\Requests\CustomerStoreRequest;
use Illuminate\Http\Response;

class CustomersStoreController extends Controller
{
    private Customer $customers;

    public function __construct(Customer $customers)
    {
        $this->middleware('auth');

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
