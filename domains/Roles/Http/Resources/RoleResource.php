<?php

namespace Domains\Roles\Http\Resources;

use Domains\Customers\Http\Resources\CustomerResource;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'name' => $this->name,
            'scopes' => $this->scopes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
            'customer' => CustomerResource::make($this->whenLoaded('customer')),
        ];
    }
}
