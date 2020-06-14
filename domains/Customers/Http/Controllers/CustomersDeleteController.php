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
        $this->authorize('delete', $this->customers);

        $this->customers->findOrFail($customerId)->delete();

        return Response::create('', Response::HTTP_NO_CONTENT);
    }
}
