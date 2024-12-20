<?php

namespace App\Filament\Widgets;

use Flowframe\Trend\Trend;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class ProductSoldChart extends ChartWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?string $maxHeight = '233px';

    protected static ?int $sort = 1;

    protected static string $color = 'warning';

    public ?string $filter = null;

    protected function getFilters(): ?array
    {
        $currentYear = now()->year;
        $years = [];

        for ($i = 0; $i <= 10; $i++) {
            $year = $currentYear - $i;
            $years[$year] = (string) $year;
        }

        return $years;
    }

    protected function getData(): array
    {
        $selectedYear = (int) ($this->filter ?? now()->year);

        $data = Trend::model(Transaction::class)
            ->dateColumn('purchase_date')
            ->between(
                start: now()->setYear($selectedYear)->startOfYear(),
                end: now()->setYear($selectedYear)->endOfYear(),
            )
            ->perMonth()
            ->sum('quantity');

        return [
            'datasets' => [
                [
                    'label' => __('models.widgets.product_sold_per_month_chart.datasets_label'),
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'fill' => 'start',
                    'tension' => 0.3,
                ],
            ],
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    public function getHeading(): string | Htmlable | null
    {
        return __('models.widgets.product_sold_per_month_chart.heading');
    }
}
