<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Transaction;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListTransactions extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->disabled(fn (): bool => Transaction::isOutOfQuota()),
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

    public function getSubheading(): ?string
    {
        return auth()->user()->role->isFree()
            ? sprintf(
                __('models.common.free_warning'),
                Transaction::FREE_LIMIT,
                __('models.transactions.title'),
                Transaction::where('user_id', auth()->id())->count(),
                __('models.transactions.title'),
            )
            : null;
    }
}
