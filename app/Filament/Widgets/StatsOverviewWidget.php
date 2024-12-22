<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Transaction;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class StatsOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 0;

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $transactions = Transaction::query()
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

        $expenses = Expense::query()
            ->when(
                $this->filters['created_from'] ?? null,
                fn(Builder $query, $date): Builder => $query->whereDate('purchase_date', '>=', $date),
            )
            ->when(
                $this->filters['created_until'] ?? null,
                fn(Builder $query, $date): Builder => $query->whereDate('purchase_date', '<=', $date),
            );

        $profit = $transactions->sum('subtotal_after_discount') - $expenses->sum('price');

        return [
            // Stat::make(
            //     label: __('models.transactions.title'),
            //     value: number_format((clone $transactions)->count()))
            //     ->icon('heroicon-m-shopping-cart')
            //     ->extraAttributes([
            //         'class' => 'bg-transaction',
            //     ]),
            Stat::make(
                label: __('models.common.sold'),
                value: number_format((clone $transactions)->sum('quantity')))
                ->icon('heroicon-o-chart-bar')
                ->extraAttributes([
                    'class' => 'bg-sold',
                ]),
            Stat::make(
                label: __('models.transactions.fields.total_sales'),
                value: __("Rp. " . number_format((clone $transactions)->sum('subtotal_after_discount'), 0, ',', '.')))
                ->icon('heroicon-o-currency-dollar')
                ->extraAttributes([
                    'class' => 'bg-sales',
                ]),
            Stat::make(
                label: __('models.expenses.title'),
                value: __("Rp. " . number_format((clone $expenses)->sum('price'), 0, ',', '.')))
                ->icon('heroicon-o-currency-dollar')
                ->extraAttributes([
                    'class' => 'bg-expense',
                ]),
            Stat::make(
                label: __('models.transactions.fields.profit'),
                value: __("Rp. " . number_format($profit, 0, ',', '.')))
                ->icon('heroicon-o-presentation-chart-line')
                ->extraAttributes([
                    'class' => 'bg-profit',
                ]),
        ];
    }
}
