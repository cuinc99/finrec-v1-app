<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Database\Eloquent\Builder;

class StatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $transactions = Transaction::query()
            ->when(
                $this->filters['is_paid'] != null,
                fn(Builder $query, $bool): Builder => $query->where('is_paid', $this->filters['is_paid']),
            )
            ->when(
                $this->filters['product_id'] ?? null,
                fn(Builder $query, $ids): Builder => $query->whereIn('product_id', $ids),
            )
            ->when(
                $this->filters['customer_id'] ?? null,
                fn(Builder $query, $ids): Builder => $query->whereIn('customer_id', $ids),
            )
            ->when(
                $this->filters['created_from'] ?? null,
                fn(Builder $query, $date): Builder => $query->whereDate('purchase_date', '>=', $date),
            )
            ->when(
                $this->filters['created_until'] ?? null,
                fn(Builder $query, $date): Builder => $query->whereDate('purchase_date', '<=', $date),
            );

        // $transactionQuantity = Trend::model(Transaction::class)
        //     ->dateColumn('purchase_date')
        //     ->between(
        //         start: now()->subYear(),
        //         end: now(),
        //     )
        //     ->perMonth()
        //     ->sum('quantity');

        // $transactionTotalSales = Trend::model(Transaction::class)
        //     ->dateColumn('purchase_date')
        //     ->between(
        //         start: now()->subYear(),
        //         end: now(),
        //     )
        //     ->perMonth()
        //     ->sum('subtotal_after_discount');

        // $transactionProfit = Trend::model(Transaction::class)
        //     ->dateColumn('purchase_date')
        //     ->between(
        //         start: now()->subYear(),
        //         end: now(),
        //     )
        //     ->perMonth()
        //     ->sum('profit');

        return [
            Stat::make(
                label: __('models.transactions.title'),
                value: number_format((clone $transactions)->count()))
                ->icon('heroicon-o-shopping-bag')
                ->extraAttributes([
                    'class' => 'bg-transaction',
                ]),
            Stat::make(
                label: __('models.common.sold'),
                value: number_format((clone $transactions)->sum('quantity')))
                // ->chart(
                //     $transactionQuantity
                //         ->map(fn(TrendValue $value) => $value->aggregate)
                //         ->toArray()
                // )
                ->color('warning')
                ->icon('heroicon-o-chart-bar')
                ->extraAttributes([
                    'class' => 'bg-sold',
                ]),
            Stat::make(
                label: __('models.transactions.fields.is_paid_options.paid'),
                value: __("Rp. " . number_format((clone $transactions)->where('is_paid', true)->sum('subtotal_after_discount'), 0, ',', '.')))
                ->icon('heroicon-o-check-circle')
                ->extraAttributes([
                    'class' => 'bg-paid',
                ]),
            Stat::make(
                label: __('models.transactions.fields.is_paid_options.unpaid'),
                value: __("Rp. " . number_format((clone $transactions)->where('is_paid', false)->sum('subtotal_after_discount'), 0, ',', '.')))
                ->icon('heroicon-o-x-circle')
                ->extraAttributes([
                    'class' => 'bg-unpaid',
                ]),
            Stat::make(
                label: __('models.transactions.fields.total_sales'),
                value: __("Rp. " . number_format((clone $transactions)->sum('subtotal_after_discount'), 0, ',', '.')))
                // ->chart(
                //     $transactionTotalSales
                //         ->map(fn(TrendValue $value) => $value->aggregate)
                //         ->toArray()
                // )
                ->color('info')
                ->icon('heroicon-o-currency-dollar')
                ->extraAttributes([
                    'class' => 'bg-sales',
                ]),
            Stat::make(
                label: __('models.transactions.fields.profit'),
                value: __("Rp. " . number_format((clone $transactions)->sum('profit'), 0, ',', '.')))
                // ->chart(
                //     $transactionProfit
                //         ->map(fn(TrendValue $value) => $value->aggregate)
                //         ->toArray()
                // )
                ->color('success')
                ->icon('heroicon-o-presentation-chart-line')
                ->extraAttributes([
                    'class' => 'bg-profit',
                ]),
        ];
    }
}
