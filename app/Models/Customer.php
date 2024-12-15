<?php

namespace App\Models;

use App\Enums\CustomerTypeEnum;
use Illuminate\Database\Eloquent\Model;
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
}
