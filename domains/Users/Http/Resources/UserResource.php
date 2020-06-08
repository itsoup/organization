<?php

namespace Domains\Users\Http\Resources;

use Domains\Customers\Http\Resources\CustomerResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'vat_number' => $this->vat_number,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'customer' => CustomerResource::make($this->whenLoaded('customer')),
        ];
    }
}
