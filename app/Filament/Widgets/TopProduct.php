<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Contracts\Support\Htmlable;

class TopProduct extends BaseWidget
{
    protected int|string|array $columnSpan = 'md';

    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductResource::getEloquentQuery()
                    ->withSum('transactions', 'quantity')
                    ->orderByDesc('transactions_sum_quantity')
                    ->limit(3)
            )
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('models.products.fields.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('sold')
                    ->label(__('models.products.fields.sold'))
                    ->suffix(' item')
                    ->alignCenter(),
            ]);
    }

    protected function getTableHeading(): string | Htmlable | null
    {
        return 'Top 3 ' . __('models.products.title') . ' ' . __('models.common.sold');
    }
}
