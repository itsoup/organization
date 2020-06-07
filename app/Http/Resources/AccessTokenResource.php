<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccessTokenResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'access_token' => $this->plainTextToken,
            'client_name' => $this->accessToken->name,
            'abilities' => $this->accessToken->abilities,
            'created_at' => $this->accessToken->created_at,
        ];
    }
}
