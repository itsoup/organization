<?php

namespace Domains\Roles\Models;

use Domains\Customers\Models\Customer;
use Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $casts = [
        'customer_id' => 'int',
        'scopes' => 'array',
    ];

    protected $fillable = [
        'customer_id',
        'name',
        'scopes',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeCustomerId(Builder $query, ?int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }
}
