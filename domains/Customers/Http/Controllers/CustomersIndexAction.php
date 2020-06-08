<?php

namespace Domains\Customers\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Customers\Models\Customer;
use Domains\Customers\Http\Resources\CustomerResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomersIndexAction extends Controller
{
    private Customer $customers;

    public function __construct(Customer $customers)
    {
        $this->middleware('auth:sanctum');

        $this->customers = $customers;
    }

    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', $this->customers);

        $resources = $this->customers
            ->when($request->input('deleted'), static fn (Builder $customers) => $customers->withTrashed())
            ->simplePaginate();

        return CustomerResource::collection($resources);
    }
}
