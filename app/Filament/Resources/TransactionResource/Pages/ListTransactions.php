<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use Filament\Actions;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\TransactionResource;
use Filament\Pages\Concerns\ExposesTableToWidgets;

class ListTransactions extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    protected function getHeaderWidgets(): array
    {
        return TransactionResource::getWidgets();
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make(trans('models.common.all')),
            __('models.transactions.fields.is_paid_options.paid') => Tab::make()->query(fn ($query) => $query->where('is_paid', true)),
            __('models.transactions.fields.is_paid_options.unpaid') => Tab::make()->query(fn ($query) => $query->where('is_paid', false)),
        ];
    }
}
