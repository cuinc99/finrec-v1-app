<?php

namespace App\Models;

use App\Enums\CustomerTypeEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    const FREE_LIMIT = 10;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => CustomerTypeEnum::class,
        ];
    }

    public static function isOutOfQuota(): bool
    {
        $user = auth()->user();

        if ($user && $user->role->isFree()) {
            return self::where('user_id', $user->id)->count() >= self::FREE_LIMIT;
        }

        return false;
    }

    public function getTotalTransactionAttribute()
    {
        return $this->transactions()->count();
    }

    public function getTotalProductsPurchasedAttribute()
    {
        return $this->transactions()->sum('quantity');
    }

    public function getTotalBuyAttribute()
    {
        return $this->transactions()->sum('subtotal_after_discount');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions($created_from = null, $created_until = null): HasMany
    {
        return $this->hasMany(Transaction::class)
            ->whereNotNull('user_id')
            ->whereNotNull('product_id')
            ->when($created_from != null && $created_until != null, fn ($q) => $q->whereBetween('purchase_date', [$created_from, $created_until]));
    }
}
