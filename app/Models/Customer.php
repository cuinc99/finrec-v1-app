<?php

namespace App\Models;

use App\Models\Transaction;
use App\Enums\CustomerTypeEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'type' => CustomerTypeEnum::class
        ];
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

    public function transactions($created_from = NULL, $created_until = NULL): HasMany
    {
        return $this->hasMany(Transaction::class)
            ->whereNotNull('user_id')
            ->whereNotNull('product_id')
            ->when($created_from != NULL && $created_until != NULL, fn ($q) => $q->whereBetween('purchase_date', [$created_from, $created_until]));
    }
}
