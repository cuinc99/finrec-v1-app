<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    const FREE_LIMIT = 6;

    protected $guarded = ['id'];

    public static function isOutOfQuota(): bool
    {
        $user = auth()->user();

        if ($user && $user->role->isFree()) {
            return self::where('user_id', $user->id)->count() >= self::FREE_LIMIT;
        }

        return false;
    }

    public function getSoldAttribute()
    {
        return $this->transactions()->sum('quantity');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions($created_from = null, $created_until = null): HasMany
    {
        return $this->hasMany(Transaction::class)
            ->whereNotNull('user_id')
            ->whereNotNull('customer_id')
            ->when($created_from != null && $created_until != null, fn ($q) => $q->whereBetween('purchase_date', [$created_from, $created_until]));
    }
}
