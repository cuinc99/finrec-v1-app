<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $guarded = ['id'];

    const FREE_LIMIT = 100;

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
