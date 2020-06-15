<?php

namespace Domains\Roles\Models;

use Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    protected $casts = [
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
}
