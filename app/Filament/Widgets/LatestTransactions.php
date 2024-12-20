<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Support\Colors\Color;
use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\TransactionResource;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTransactions extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = null;

    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(TransactionResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('purchase_date', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('is_paid')
                    ->label(__('models.transactions.fields.is_paid'))
                    ->formatStateUsing(fn(bool $state) => $state ? __('models.transactions.fields.is_paid_options.paid') : __('models.transactions.fields.is_paid_options.unpaid'))
                    ->badge()
                    ->color(fn(bool $state) => match ($state) {
                        true => Color::Sky,
                        false => Color::Red,
                    }),
                Tables\Columns\TextColumn::make('purchase_date')
                    ->label(__('models.transactions.fields.purchase_date'))
                    ->date()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label(__('models.transactions.fields.customer')),
                Tables\Columns\TextColumn::make('customer.type')
                    ->label(__('models.customers.fields.type'))
                    ->badge(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('models.transactions.fields.product'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('models.transactions.fields.price'))
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.'))),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('models.transactions.fields.quantity'))
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('discount_per_item')
                    ->label(__('models.transactions.fields.discount_per_item'))
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.'))),
                Tables\Columns\TextColumn::make('total_discount_per_item')
                    ->label(__('models.transactions.fields.total_discount_per_item'))
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.'))),
                Tables\Columns\TextColumn::make('discount')
                    ->label(__('models.transactions.fields.discount'))
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.'))),
                Tables\Columns\TextColumn::make('total_discount')
                    ->label(__('models.transactions.fields.total_discount'))
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.'))),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label(__('models.transactions.fields.subtotal'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.'))),
                Tables\Columns\TextColumn::make('subtotal_after_discount')
                    ->label(__('models.transactions.fields.subtotal_after_discount'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                    ->weight('bold')
                    ->color(Color::Blue),
                Tables\Columns\TextColumn::make('capital')
                    ->label(__('models.transactions.fields.capital'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                    ->color(Color::Red),
                Tables\Columns\TextColumn::make('profit')
                    ->label(__('models.transactions.fields.profit'))
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                    ->searchable()
                    ->sortable()
                    ->color(Color::Teal),
            ]);
            // ->actions([
            //     Tables\Actions\Action::make('open')
            //         ->url(fn (Transaction $record): string => TransactionResource::getUrl('edit', ['record' => $record])),
            // ]);
    }

    protected function getTableHeading(): string | Htmlable | null
    {
        return __('models.transactions.title') . ' ' . __('models.common.latest');
    }
}
