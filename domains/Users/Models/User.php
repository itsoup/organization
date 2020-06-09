<?php

namespace Domains\Users\Models;

use Domains\Customers\Models\Customer;
use Domains\Users\Casts\PasswordCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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
        'password', 'remember_token',
    ];

    protected $casts = [
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

    public function revokePreviousTokens(string $name): void
    {
        $this->tokens()
            ->where('name', $name)
            ->delete();
    }
}
