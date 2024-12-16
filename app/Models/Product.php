<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $guarded = ['id'];

    public function getSoldAttribute()
    {
        return $this->transactions()->sum('qty');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions($created_from = NULL, $created_until = NULL): HasMany
    {
        return $this->hasMany(Transaction::class)
            ->whereNotNull('user_id')
            ->whereNotNull('customer_id')
            ->when($created_from != NULL && $created_until != NULL, fn ($q) => $q->whereBetween('purchase_date', [$created_from, $created_until]));
    }
}
