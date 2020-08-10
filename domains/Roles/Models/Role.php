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

    protected const SCOPES = [
        'organization:customers:view',
        'organization:customers:manage',
        'organization:roles:view',
        'organization:roles:manage',
        'organization:users:view',
        'organization:users:manage',
        'assets-active-directory:locations:view',
        'assets-active-directory:locations:manage',
        'assets-active-directory:assets:view',
        'assets-active-directory:assets:manage',
        'assets-active-directory:properties:view',
        'assets-active-directory:properties:manage',
    ];

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

    public static function getValidScopesFor(string $accountType): array
    {
        if ($accountType === 'user') {
            return collect(self::SCOPES)
                ->reject(static function ($scope) {
                    return str_starts_with($scope, 'organization:customers');
                })
                ->values()
                ->all();
        }

        return self::SCOPES;
    }
}
