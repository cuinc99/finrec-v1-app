<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Contracts\Support\Htmlable;

class SalesAndExpensesChart extends ChartWidget
{
    protected static ?string $pollingInterval = null;

    protected static ?string $maxHeight = '233px';

    protected static ?int $sort = 3;

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

        $dataSales = Trend::model(Transaction::class)
            ->dateColumn('purchase_date')
            ->between(
                start: now()->setYear($selectedYear)->startOfYear(),
                end: now()->setYear($selectedYear)->endOfYear(),
            )
            ->perMonth()
            ->sum('subtotal_after_discount');

        $dataExpenses = Trend::model(Expense::class)
            ->dateColumn('purchase_date')
            ->between(
                start: now()->setYear($selectedYear)->startOfYear(),
                end: now()->setYear($selectedYear)->endOfYear(),
            )
            ->perMonth()
            ->sum('price');

        return [
            'datasets' => [
                [
                    'label' => __('models.widgets.sales_expenses_per_month_chart.datasets_label_sales'),
                    'data' => $dataSales->map(fn(TrendValue $value) => $value->aggregate),
                    'fill' => 'start',
                    'tension' => 0.3,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                ],
                [
                    'label' => __('models.widgets.sales_expenses_per_month_chart.datasets_label_expenses'),
                    'data' => $dataExpenses->map(fn(TrendValue $value) => $value->aggregate),
                    'fill' => 'start',
                    'tension' => 0.3,
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
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
        return __('models.widgets.sales_expenses_per_month_chart.heading');
    }
}
