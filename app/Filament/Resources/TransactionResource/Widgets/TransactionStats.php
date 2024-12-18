<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Filament\Resources\TransactionResource\Pages\ListTransactions;
use App\Models\Transaction;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class TransactionStats extends BaseWidget
{
    use InteractsWithPageTable;

    protected static ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ListTransactions::class;
    }

    protected function getStats(): array
    {
        $transactionData = Trend::model(Transaction::class)
            ->between(
                start: now()->subYear(),
                end: now(),
            )
            ->perMonth()
            ->count();

        return [
            Stat::make(__('models.transactions.title'), $this->getPageTableQuery()->count())
                ->chart(
                    $transactionData
                        ->map(fn(TrendValue $value) => $value->aggregate)
                        ->toArray()
                )
                ->description($this->getPageTableQuery()->where('purchase_date', today())->count() . ' ' . __('models.common.today'))
                ->descriptionIcon('heroicon-m-arrow-trending-up', IconPosition::Before),
            Stat::make(__('models.transactions.fields.is_paid_options.paid'), __("Rp. " . number_format($this->getPageTableQuery()->where('is_paid', true)->sum('subtotal_after_discount'), 0, ',', '.'))),
            Stat::make(__('models.transactions.fields.is_paid_options.unpaid'), __("Rp. " . number_format($this->getPageTableQuery()->where('is_paid', false)->sum('subtotal_after_discount'), 0, ',', '.'))),
            Stat::make(__('models.transactions.fields.profit'), __("Rp. " . number_format($this->getPageTableQuery()->sum('profit'), 0, ',', '.'))),
        ];
    }
}
