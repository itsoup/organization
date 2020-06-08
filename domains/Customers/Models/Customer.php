<?php

namespace Domains\Customers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vat_number',
        'name',
        'address',
        'country',
        'logo',
    ];
}
