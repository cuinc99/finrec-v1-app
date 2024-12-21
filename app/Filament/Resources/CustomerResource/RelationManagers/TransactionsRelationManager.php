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
            ->actions([]);
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Data ' . __('models.transactions.title');
    }
}
