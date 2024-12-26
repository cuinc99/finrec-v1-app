<?php

namespace App\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserRoleEnum: string implements HasLabel, HasColor, HasIcon
{
    case ADMIN = 'Admin';
    case USER = 'User';
    case FREE = 'Free';

    public function getLabel(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::USER => 'User',
            self::FREE => 'Free',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::ADMIN => 'success',
            self::USER => 'info',
            self::FREE => 'secondary',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::ADMIN => 'heroicon-m-user-group',
            self::USER => 'heroicon-m-user',
            self::FREE => 'heroicon-m-user',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isUser(): bool
    {
        return $this === self::USER;
    }

    public function isFree(): bool
    {
        return $this === self::FREE;
    }

}
