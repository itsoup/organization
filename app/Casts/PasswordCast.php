<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;
use Illuminate\Support\Facades\Hash;

class PasswordCast implements CastsInboundAttributes
{
    public function set($model, string $key, $value, array $attributes)
    {
        return Hash::make($value);
    }
}
