<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use App\Models\Expense;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        $expenseCode = Str::random(2).rand(10, 99).Str::random(2).rand(10, 99);
        $expenses = $data;
        $firstExpense = null;

        foreach ($expenses['items'] as $expense) {
            $newExpense = Expense::create([
                'expense_code' => $expenseCode,
                'purchase_date' => $expenses['purchase_date'],
                'product' => $expense['product'],
                'price' => $expense['price'],
                'user_id' => $expenses['user_id'],
            ]);

            if (! $firstExpense) {
                $firstExpense = $newExpense;
            }
        }

        return $firstExpense;
    }
}
