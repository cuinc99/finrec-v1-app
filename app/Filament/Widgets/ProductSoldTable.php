<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class ProductSoldTable extends BaseWidget
{
    protected int|string|array $columnSpan = 'md';

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
            ->query(Product::query())
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('models.products.title')),
                ...collect(range(1, 12))->map(function ($month) {
                    return Tables\Columns\TextColumn::make(date('M', mktime(0, 0, 0, $month, 1)))
                        ->label(date('M', mktime(0, 0, 0, $month, 1)))
                        ->default(function (Product $record) use ($month) {
                            $total = $record->transactions()
                                ->when($this->year, function ($query, $year) {
                                    return $query->whereYear('purchase_date', $year);
                                })
                                ->whereMonth('purchase_date', $month)
                                ->sum('quantity');

                            return $total;
                        })
                        ->alignCenter();
                }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('year')
                    ->label(__('models.common.year'))
                    ->options($this->getAvailableYears())
                    ->default($this->year)
                    ->query(function ($query, $data) {
                        $this->year = $data['value'];

                        return $query;
                    })
                    ->selectablePlaceholder(false),
            ])
            ->filtersTriggerAction(
                fn (Tables\Actions\Action $action) => $action
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

    protected function getTableHeading(): string|Htmlable|null
    {
        return __('models.widgets.product_sold_per_month_chart.heading_table');
    }
}
