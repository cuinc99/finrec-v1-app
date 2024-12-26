<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProducts extends ManageRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->disabled(fn(): bool => Product::isOutOfQuota()),
        ];
    }

    public function getSubheading(): string
    {
        return auth()->user()->role->isFree()
            ? sprintf(
                __('models.common.free_warning'),
                Product::FREE_LIMIT,
                __('models.products.title'),
                Product::where('user_id', auth()->id())->count(),
                __('models.products.title'),
            )
            : null;
    }
}
