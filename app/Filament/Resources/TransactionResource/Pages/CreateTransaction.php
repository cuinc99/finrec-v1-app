<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Models\Transaction;
use Illuminate\Support\Str;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\TransactionResource;

class CreateTransaction extends CreateRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        $transactionCode = Str::random(2) . rand(10, 99) . Str::random(2) . rand(10, 99);
        $transactions = $data;
        $firstTransaction = null;

        foreach ($transactions['items'] as $transaction) {
            $newTransaction = Transaction::create([
                'transaction_code' => $transactionCode,
                "purchase_date" => $transactions['purchase_date'],
                "quantity" => $transaction['quantity'],
                "price" => $transaction['price'],
                "discount_per_item" => $transaction['discount_per_item'],
                "total_discount_per_item" => $transaction['total_discount_per_item'],
                "discount" => $transaction['discount'],
                "total_discount" => $transaction['total_discount'],
                "subtotal" => $transaction['subtotal'],
                "subtotal_after_discount" => $transaction['subtotal_after_discount'],
                "capital_per_item" => $transaction['capital_per_item'],
                "capital" => $transaction['capital'],
                "profit_per_item" => $transaction['profit_per_item'],
                "profit" => $transaction['profit'],
                "is_paid" => $transaction['is_paid'],
                "user_id" => $transactions['user_id'],
                "customer_id" => $transactions['customer_id'],
                "product_id" => $transaction['product_id'],
            ]);

            if (!$firstTransaction) {
                $firstTransaction = $newTransaction;
            }
        }

        return $firstTransaction;
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
}
