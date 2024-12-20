<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Widgets\TableWidget as BaseWidget;

class ProductSoldTable extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        $currentYear = date('Y');

        return $table
            ->query(
                Transaction::query()
                    ->select([
                        DB::raw('MONTH(purchase_date) as month'),
                        DB::raw('YEAR(purchase_date) as year'),
                        DB::raw('COUNT(*) as total_transactions'),
                        DB::raw('SUM(quantity) as total_products'),
                        DB::raw('SUM(subtotal_after_discount) as total_sales'),
                        DB::raw('SUM(profit) as total_profit'),
                    ])
                    ->groupBy('year', 'month')
                    ->orderBy('year', 'asc')
                    ->orderBy('month', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('month')
                    ->label(__('models.common.month'))
                    ->formatStateUsing(fn ($state) => date('F', mktime(0, 0, 0, $state, 1))),
                Tables\Columns\TextColumn::make('year')
                    ->label(__('models.common.year')),
                Tables\Columns\TextColumn::make('total_transactions')
                    ->label(__('models.transactions.fields.total_transactions'))
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_products')
                    ->label(__('models.products.fields.sold'))
                    ->suffix(' item')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_sales')
                    ->label(__('models.transactions.fields.total_sales'))
                    ->formatStateUsing(fn ($state) => "Rp. " . number_format($state, 0, ',', '.'))
                    ->color(Color::Blue),
                Tables\Columns\TextColumn::make('total_profit')
                    ->label(__('models.transactions.fields.profit'))
                    ->formatStateUsing(fn ($state) => "Rp. " . number_format($state, 0, ',', '.'))
                    ->color(Color::Teal),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('year')
                    ->label(__('models.common.year'))
                    ->options(
                        Transaction::query()
                            ->selectRaw('YEAR(purchase_date) as year')
                            ->distinct()
                            ->pluck('year', 'year')
                            ->toArray()
                    )
                    ->default($currentYear)
                    ->query(function ($query, $data) {
                        return $data['value'] ? $query->whereYear('purchase_date', $data['value']) : $query;
                    })
                    ->selectablePlaceholder(false)
            ]);
    }

    public function getTableRecordKey($record): string
    {
        return $record->year . '-' . str_pad($record->month, 2, '0', STR_PAD_LEFT);
    }


    protected function getTableHeading(): string | Htmlable | null
    {
        return __('models.widgets.product_sold_per_month_chart.heading_table');
    }
}
