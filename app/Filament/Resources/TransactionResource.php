<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-m-shopping-cart';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
                    ->money(__('models.common.money_locale')),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('models.transactions.fields.quantity')),
                Tables\Columns\TextColumn::make('discount')
                    ->label(__('models.transactions.fields.discount'))
                    ->money(__('models.common.money_locale')),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label(__('models.transactions.fields.subtotal'))
                    ->searchable()
                    ->sortable()
                    ->money(__('models.common.money_locale')),
                Tables\Columns\TextColumn::make('subtotal_after_discount')
                    ->label(__('models.transactions.fields.subtotal_after_discount'))
                    ->searchable()
                    ->sortable()
                    ->money(__('models.common.money_locale'))
                    ->weight('bold')
                    ->color(Color::Blue),
                Tables\Columns\TextColumn::make('total_capital')
                    ->label(__('models.transactions.fields.total_capital'))
                    ->searchable()
                    ->sortable()
                    ->money(__('models.common.money_locale'))
                    ->color(Color::Red),
                Tables\Columns\TextColumn::make('profit')
                    ->label(__('models.transactions.fields.profit'))
                    ->money(__('models.common.money_locale'))
                    ->searchable()
                    ->sortable()
                    ->color(Color::Teal),
            ])
            ->filters([
                //
            ])
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
