<?php

namespace Domains\Customers\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Customers\Models\Customer;
use Domains\Customers\Http\Resources\CustomerResource;

class CustomersShowController extends Controller
{
    private Customer $customers;

    public function __construct(Customer $customers)
    {
        $this->middleware('auth');

        $this->customers = $customers;
    }

    public function __invoke(int $customerId): CustomerResource
    {
        $resource = $this->customers
            ->withTrashed()
            ->findOrFail($customerId);

        $this->authorize('view', $resource);

        return CustomerResource::make($resource);
    }
}
