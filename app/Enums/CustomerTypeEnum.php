<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum CustomerTypeEnum: string implements HasColor, HasIcon, HasLabel
{
    case RESELLER = 'Reseller';
    case PEMBELI = 'Pembeli';

    public function getLabel(): string
    {
        return match ($this) {
            self::RESELLER => 'Reseller',
            self::PEMBELI => 'Pembeli',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::RESELLER => 'success',
            self::PEMBELI => 'info',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::RESELLER => 'heroicon-m-user-group',
            self::PEMBELI => 'heroicon-m-user',
        };
    }
}
