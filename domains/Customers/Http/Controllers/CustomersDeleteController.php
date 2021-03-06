<?php

namespace Domains\Customers\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Customers\Models\Customer;
use Illuminate\Http\Response;

class CustomersDeleteController extends Controller
{
    private Customer $customers;

    public function __construct(Customer $customers)
    {
        $this->middleware('auth');

        $this->customers = $customers;
    }

    public function __invoke(int $customerId)
    {
        $resource = $this->customers->findOrFail($customerId);

        $this->authorize('delete', $resource);

        $resource->delete();

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
