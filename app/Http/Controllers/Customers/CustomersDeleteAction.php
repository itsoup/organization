<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Response;

class CustomersDeleteAction extends Controller
{
    private Customer $customers;

    public function __construct(Customer $customers)
    {
        $this->middleware('auth:sanctum');

        $this->customers = $customers;
    }

    public function __invoke(int $customerId)
    {
        $this->authorize('delete', $this->customers);

        $this->customers->findOrFail($customerId)->delete();

        return Response::create('', Response::HTTP_NO_CONTENT);
    }
}
