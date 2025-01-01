<?php

namespace App\Models;

class Expense extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
        ];
    }
}
