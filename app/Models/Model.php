<?php

namespace App\Models;

use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Support\Str;

class Model extends BaseModel
{
    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        if (Str::contains(static::class, 'App\Models\User')) {
            return;
        }

        static::addGlobalScope(new UserScope);
    }
}
