<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Http\Resources\CustomerResource;

class CustomersShowAction extends Controller
{
    private Customer $customers;

    public function __construct(Customer $customers)
    {
        $this->middleware('auth:sanctum');

        $this->customers = $customers;
    }

    public function __invoke(int $customerId): CustomerResource
    {
        $this->authorize('view', $this->customers);

        $resource = $this->customers
            ->withTrashed()
            ->findOrFail($customerId);

        return CustomerResource::make($resource);
    }
}
