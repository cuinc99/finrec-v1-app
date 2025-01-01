<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Filament\Resources\TransactionResource;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

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
            ->actions([]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Data '.__('models.transactions.title');
    }
}
