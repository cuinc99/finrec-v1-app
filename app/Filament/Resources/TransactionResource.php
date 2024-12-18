<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Transaction;
use App\Enums\CustomerTypeEnum;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\Widgets\TransactionStats;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-m-shopping-cart';

    public static function table(Table $table): Table
    {
        return $table
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
                    ->formatStateUsing(fn (string $state): string => __("Rp. " . number_format($state, 0, ',', '.'))),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('models.transactions.fields.quantity'))
                    ->alignCenter()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total ' . __('models.transactions.fields.quantity')),
                    ]),
                Tables\Columns\TextColumn::make('discount')
                    ->label(__('models.transactions.fields.discount'))
                    ->formatStateUsing(fn (string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn (string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                            ->label('Total ' . __('models.transactions.fields.discount')),
                    ]),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label(__('models.transactions.fields.subtotal'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn (string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                            ->label('Total ' . __('models.transactions.fields.subtotal')),
                    ]),
                Tables\Columns\TextColumn::make('subtotal_after_discount')
                    ->label(__('models.transactions.fields.subtotal_after_discount'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                    ->weight('bold')
                    ->color(Color::Blue)
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn (string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                            ->label('Total ' . __('models.transactions.fields.subtotal_after_discount')),
                    ]),
                Tables\Columns\TextColumn::make('capital')
                    ->label(__('models.transactions.fields.capital'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                    ->color(Color::Red)
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn (string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                            ->label(__('models.transactions.fields.capital')),
                    ]),
                Tables\Columns\TextColumn::make('profit')
                    ->label(__('models.transactions.fields.profit'))
                    ->formatStateUsing(fn (string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                    ->searchable()
                    ->sortable()
                    ->color(Color::Teal)
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn (string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                            ->label('Total ' . __('models.transactions.fields.profit')),
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label(__('models.transactions.fields.product'))
                    ->options(auth()->user()->products()->pluck('name', 'id'))
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('customer_id')
                    ->label(__('models.transactions.fields.customer'))
                    ->options(auth()->user()->customers()->pluck('name', 'id'))
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('customer_type')
                    ->label(__('models.customers.fields.type') . ' ' . __('models.transactions.fields.customer'))
                    ->options(CustomerTypeEnum::class)
                    ->searchable()
                    ->modifyQueryUsing(function ($query, $data) {
                        if (! empty($data['value'])) {
                            $query->whereHas('customer', function ($query) use ($data) {
                                $query->where('type', $data);
                            });
                        }
                    }),
                Tables\Filters\Filter::make('purchase_date')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('models.common.created_from'))
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->prefixIcon('heroicon-m-calendar-days'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('models.common.created_until'))
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->prefixIcon('heroicon-m-calendar-days'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('purchase_date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('purchase_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = __('models.common.created_from') . ' ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = __('models.common.created_until') . ' ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ], layout: FiltersLayout::Modal)
            ->filtersFormSchema(fn(array $filters): array=> [
                $filters['product_id'],
                $filters['customer_id'],
                $filters['customer_type'],
                Forms\Components\Section::make('Tanggal Pembelian')
                    ->schema([
                        $filters['purchase_date'],
                    ]),
            ])
            ->deferFilters()
            ->persistFiltersInSession()
            ->filtersTriggerAction(
                fn (Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->button()
                    ->color(Color::Red)
                    ->size(ActionSize::Small),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            TransactionStats::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()
            ->where('user_id', auth()->user()->id)
            ->latest('purchase_date');
    }

    public static function getLabel(): string
    {
        return __('models.transactions.title');
    }

    public static function canAccess(): bool
    {
        return auth()->user()->role->isUser();
    }
}
