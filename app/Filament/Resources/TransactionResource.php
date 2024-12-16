<?php

namespace App\Filament\Resources;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TransactionResource\Pages;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

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
                Tables\Columns\TextColumn::make('qty')
                    ->label(__('models.transactions.fields.qty')),
                Tables\Columns\TextColumn::make('discount_price')
                    ->label(__('models.transactions.fields.discount_price'))
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
                    ->size('lg')
                    ->weight('bold')
                    ->color('warning'),
                Tables\Columns\TextColumn::make('profit_price')
                    ->label(__('models.transactions.fields.profit_price'))
                    ->money(__('models.common.money_locale')),
                 Tables\Columns\TextColumn::make('pay')
                    ->label(__('models.transactions.fields.pay'))
                    ->money(__('models.common.money_locale'))
                    ->size('lg')
                    ->weight('bold')
                    ->color('danger'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            ->latest();
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
