<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Http\Requests\CustomerUpdateRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class CustomersUpdateAction extends Controller
{
    private Customer $customers;

    public function __construct(Customer $customers)
    {
        $this->middleware('auth:sanctum');

        $this->customers = $customers;
    }

    public function __invoke(CustomerUpdateRequest $request, int $customerId)
    {
        $resource = $this->customers->findOrFail($customerId);

        if ($request->hasFile('logo')) {
            $newLogoPath = $request->file('logo')->store('customers');

            if ($resource->logo !== null) {
                Storage::delete($resource->logo);
            }
        }

        $resource->update([
            'vat_number' => $request->input('vat_number'),
            'name' => $request->input('name'),
            'address' => $request->input('address'),
            'country' => $request->input('country'),
            'logo' => $newLogoPath ?? $resource->logo,
        ]);

        return Response::create('', Response::HTTP_NO_CONTENT);
    }
}
