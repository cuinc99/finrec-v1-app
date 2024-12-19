<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\ActionSize;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\TransactionResource;
use Filament\Resources\RelationManagers\RelationManager;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    public function form(Form $form): Form
    {
        return TransactionResource::form($form);
    }

    public function table(Table $table): Table
    {
        return TransactionResource::table($table)
            ->actions([
                Tables\Actions\Action::make('paid')
                    ->label('Set ' . __('models.transactions.fields.is_paid_options.paid'))
                    ->requiresConfirmation()
                    ->visible(fn(Transaction $record) => !$record->is_paid)
                    ->action(fn(Transaction $record) => $record->update(['is_paid' => true]))
                    ->button()
                    ->icon('heroicon-m-check')
                    ->color(Color::Sky)
                    ->size(ActionSize::Small)
                    ->tooltip(__('models.common.set') . ' ' . __('models.transactions.fields.is_paid_options.paid')),
            ]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Data ' . __('models.transactions.title');
    }
}
