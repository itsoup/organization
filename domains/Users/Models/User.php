<?php

namespace Domains\Users\Models;

use Domains\Customers\Models\Customer;
use Domains\Roles\Models\Role;
use Domains\Users\Casts\PasswordCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use League\OAuth2\Server\Entities\UserEntityInterface;

class User extends Authenticatable implements UserEntityInterface
{
    use HasApiTokens;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'email',
        'name',
        'password',
        'phone',
        'vat_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'customer_id' => 'int',
        'email_verified_at' => 'datetime',
        'password' => PasswordCast::class,
    ];

    public function scopeEmail(Builder $query, string $email): Builder
    {
        return $query->where('email', $email);
    }

    public function scopeCustomerId(Builder $query, int $customerId): Builder
    {
        return $query->where('customer_id', $customerId);
    }

    public function isSystemOperator(): bool
    {
        return $this->customer_id === null;
    }

    public function isUser(): bool
    {
        return $this->customer_id !== null;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->getAuthIdentifier();
    }

    public function getAccountTypeAttribute(): string
    {
        if ($this->isSystemOperator()) {
            return 'system-operator';
        }

        return 'user';
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}
