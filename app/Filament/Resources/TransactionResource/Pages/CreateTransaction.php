<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Str;

class CreateTransaction extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string $resource = TransactionResource::class;

    protected static string $view = 'filament.resources.transaction-resource.pages.create-transaction';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $title = 'Buat Transaksi';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->id())
                            ->required(),
                        Forms\Components\DatePicker::make('purchase_date')
                            ->label('Tanggal transaksi')
                            ->maxDate(now()->addDay())
                            ->required(),
                        Forms\Components\Select::make('customer_id')
                            ->label('Customer')
                            ->options(Customer::all()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),

                        TableRepeater::make('items')
                            ->columnSpanFull()
                            ->headers([
                                Header::make('product')->label('Produk'),
                                Header::make('price')->label('Harga'),
                                Header::make('qty')->label('Qty'),
                                Header::make('discount_price')->label('Discount_price'),
                                Header::make('subtotal')->label('Subtotal'),
                                Header::make('subtotal_after_discount')->label('Subtotal_after_discount'),
                                Header::make('profit_price')->label('Profit_price'),
                            ])
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->options(auth()->user()->products->pluck('name', 'id'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                                        $qty = $get('qty');
                                        $price = Product::find($state)->purchase_price ?? 0;
                                        $subtotal = $price * $qty;
                                        $discount_price = $subtotal;
                                        $subtotal_after_discount = $subtotal - $discount_price;
                                        $profit_price = $subtotal;

                                        $set('price', $price);
                                        $set('discount_price', $discount_price);
                                        $set('subtotal', $subtotal);
                                        $set('subtotal_after_discount', $subtotal_after_discount);
                                        $set('profit_price', $profit_price);
                                    })
                                    ->required(),

                                // Forms\Components\Placeholder::make("p_price")
                                //     ->label('Harga')
                                //     ->content(function (Get $get): string {
                                //         $price = $get('price');
                                //         return __("Rp. " . number_format($price, 0, ',', '.'));
                                //     }),
                                Forms\Components\TextInput::make('price')
                                    ->label('Harga')
                                    ->disabled()
                                    ->required(),

                                Forms\Components\TextInput::make('qty')
                                    ->label('Qty')
                                    ->minValue(1)
                                    ->default(1)
                                    ->live()
                                    ->integer()
                                    ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                                        $product_id = $get('product_id');
                                        $price = Product::find($product_id)->purchase_price ?? 0;
                                        $subtotal = $price * $state;
                                        $discount_price = $subtotal;
                                        $subtotal_after_discount = $subtotal - $discount_price;
                                        $profit_price = $subtotal;

                                        $set('discount_price', $discount_price);
                                        $set('subtotal', $subtotal);
                                        $set('subtotal_after_discount', $subtotal_after_discount);
                                        $set('profit_price', $profit_price);
                                    })
                                    ->disabled(function (Get $get) {
                                        return !$get('product_id');
                                    })
                                    ->required(),

                                Forms\Components\TextInput::make('discount_price')
                                    ->label('Diskon (Rp)')
                                    ->required(),

                                Forms\Components\Placeholder::make("p_subtotal")
                                    ->label('Subtotal')
                                    ->content(function (Get $get) {
                                        $subtotal = $get('subtotal');
                                        return __("Rp. " . number_format($subtotal, 0, ',', '.'));
                                    }),
                                Forms\Components\Hidden::make('subtotal')
                                    ->label('Subtotal')
                                    ->disabled()
                                    ->required(),

                                Forms\Components\Placeholder::make("p_subtotal_after_discount")
                                    ->label('Subtotal setelah diskon')
                                    ->content(function (Get $get) {
                                        $subtotal_after_discount = $get('subtotal_after_discount');
                                        return __("Rp. " . number_format($subtotal_after_discount, 0, ',', '.'));
                                    }),
                                Forms\Components\Hidden::make('subtotal_after_discount')
                                    ->label('Subtotal setelah diskon')
                                    ->disabled()
                                    ->required(),

                                Forms\Components\Placeholder::make("p_profit_price")
                                    ->label('Profit (Rp)')
                                    ->content(function (Get $get) {
                                        $profit_price = $get('profit_price');
                                        return __("Rp. " . number_format($profit_price, 0, ',', '.'));
                                    }),
                                Forms\Components\Hidden::make('profit_price')
                                    ->label('Profit (Rp)')
                                    ->disabled()
                                    ->required(),
                            ]),
                    ])->columns(2),
            ])->statePath('data');
    }

    public function save()
    {
        try {
            $transactionCode = Str::random(2) . rand(10, 99) . Str::random(2) . rand(10, 99);
            $transactions = $this->form->getState();

            dd($transactions);

            foreach ($transactions['items'] as $transaction) {
                Transaction::create([
                    'transaction_code' => $transactionCode,
                    "user_id" => $transactions['user_id'],
                    "purchase_date" => $transactions['purchase_date'],
                    "customer_id" => $transactions['customer_id'],

                    "product_id" => $transaction['product_id'],
                    "qty" => $transaction['qty'],
                    "price" => $transaction['price'],
                    "discount_price" => $transaction['discount_price'],
                    "subtotal" => $transaction['subtotal'],
                    "subtotal_after_discount" => $transaction['subtotal_after_discount'],
                    "profit_price" => $transaction['profit_price'],
                ]);
            }

            Notification::make()
                ->success()
                ->title('Transaksi berhasil dibuat')
                ->send();

            return redirect('/admin');
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan')
                ->submit('save'),
        ];
    }
}
