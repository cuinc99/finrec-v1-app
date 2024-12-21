<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Widgets\TableWidget as BaseWidget;

class SalesAndExpensesTable extends BaseWidget
{
    protected int | string | array $columnSpan = 'md';

    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 5;

    protected $year;

    public function mount()
    {
        $this->year = self::getCurrentYear();
    }

    public static function getCurrentYear(): int
    {
        return Carbon::now()->year;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()
                    ->select([
                        DB::raw('MONTH(purchase_date) as month'),
                        DB::raw('YEAR(purchase_date) as year'),
                        DB::raw('COUNT(*) as total_transactions'),
                        DB::raw('SUM(quantity) as total_products'),
                        DB::raw('SUM(subtotal_after_discount) as total_sales'),
                        // DB::raw('SUM(profit) as total_profit'),
                    ])
                    ->groupBy('year', 'month')
                    ->orderBy('year', 'asc')
                    ->orderBy('month', 'asc')
            )
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\TextColumn::make('month')
                    ->label(__('models.common.month'))
                    ->formatStateUsing(fn ($state) => date('F', mktime(0, 0, 0, $state, 1))),
                Tables\Columns\TextColumn::make('year')
                    ->label(__('models.common.year'))
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_transactions')
                    ->label(__('models.transactions.fields.total_transactions'))
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_products')
                    ->label(__('models.products.fields.sold'))
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_sales')
                    ->label(__('models.transactions.fields.total_sales'))
                    ->formatStateUsing(fn ($state) => "Rp. " . number_format($state, 0, ',', '.'))
                    ->color(Color::Blue),
                // Tables\Columns\TextColumn::make('total_profit')
                //     ->label(__('models.transactions.fields.profit'))
                //     ->formatStateUsing(fn ($state) => "Rp. " . number_format($state, 0, ',', '.'))
                //     ->color(Color::Teal),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('year')
                    ->label(__('models.common.year'))
                    ->options($this->getAvailableYears())
                    ->default($this->year)
                    ->query(function ($query, $data) {
                        return $data['value'] ? $query->whereYear('purchase_date', $data['value']) : $query;
                    })
                    ->selectablePlaceholder(false)
            ])
            ->filtersTriggerAction(
                fn(Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Filter'),
            );
    }

    protected function getAvailableYears()
    {
        $years = DB::table('transactions')
            ->select(DB::raw('DISTINCT YEAR(purchase_date) as year'))
            ->whereNotNull('purchase_date')
            ->orderBy('year', 'desc')
            ->pluck('year', 'year')
            ->toArray();

        if (empty($years)) {
            $years = [self::getCurrentYear() => self::getCurrentYear()];
        }

        return $years;
    }

    public function getTableRecordKey($record): string
    {
        return $record->year . '-' . str_pad($record->month, 2, '0', STR_PAD_LEFT);
    }


    protected function getTableHeading(): string | Htmlable | null
    {
        return __('models.widgets.sales_expenses_per_month_chart.heading_table');
    }
}
