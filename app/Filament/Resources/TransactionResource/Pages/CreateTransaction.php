<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use Filament\Forms;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Customer;
use Filament\Forms\Form;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use Awcodes\TableRepeater\Header;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Notifications\Notification;
use App\Filament\Resources\TransactionResource;
use Awcodes\TableRepeater\Components\TableRepeater;

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
                            ->label(__('models.transactions.fields.purchase_date'))
                            ->maxDate(now()->addDay())
                            ->required(),
                        Forms\Components\Select::make('customer_id')
                            ->label(__('models.customers.title'))
                            ->options(Customer::where('user_id', auth()->id())->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])->columns(2),
                Forms\Components\Section::make('Transaksi')
                    ->schema([
                        TableRepeater::make('items')
                            ->hiddenLabel()
                            ->columnSpan('full')
                            ->headers([
                                Header::make('product')->label(__('models.transactions.fields.product')),
                                Header::make('price')->label(__('models.transactions.fields.price')),
                                Header::make('qty')->label(__('models.transactions.fields.qty')),
                                Header::make('discount_price')->label(__('models.transactions.fields.discount_price')),
                                Header::make('subtotal')->label(__('models.transactions.fields.subtotal')),
                                Header::make('subtotal_after_discount')->label(__('models.transactions.fields.subtotal_after_discount')),
                                Header::make('profit_price')->label(__('models.transactions.fields.profit_price')),
                            ])
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label(__('models.transactions.fields.product'))
                                    ->options(Product::where('user_id', auth()->id())->pluck('name', 'id'))
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                                        $product = Product::find($state);
                                        $qty = $get('qty');
                                        $purchase_price = $product?->purchase_price;
                                        $selling_price = $product?->selling_price;
                                        $subtotal = $purchase_price * $qty;
                                        $subtotal_after_discount = $subtotal - $get('discount_price');
                                        $profit_price = $subtotal;

                                        $set('price', $purchase_price);
                                        $set('selling_price', $selling_price);
                                        $set('subtotal', $subtotal);
                                        $set('subtotal_after_discount', $subtotal_after_discount);
                                        $set('profit_price', $profit_price);
                                    })
                                    ->required(),

                                Forms\Components\Placeholder::make("p_price")
                                    ->label(__('models.transactions.fields.price'))
                                    ->hiddenLabel()
                                    ->content(function (Get $get): string {
                                        $price = $get('price');
                                        return __("Rp. " . number_format($price, 0, ',', '.'));
                                    }),
                                Forms\Components\Hidden::make('price')
                                    ->disabled()
                                    ->required(),
                                Forms\Components\Hidden::make('selling_price')
                                    ->disabled()
                                    ->required(),

                                Forms\Components\TextInput::make('qty')
                                    ->label(__('models.transactions.fields.qty'))
                                    ->minValue(1)
                                    ->default(1)
                                    ->live()
                                    ->integer()
                                    ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                                        $product_id = $get('product_id');
                                        $price = Product::find($product_id)->purchase_price ?? 0;
                                        $subtotal = $price * $state;
                                        $subtotal_after_discount = $subtotal - $get('discount_price');
                                        $profit_price = $subtotal;

                                        $set('subtotal', $subtotal);
                                        $set('subtotal_after_discount', $subtotal_after_discount);
                                        $set('profit_price', $profit_price);
                                    })
                                    ->disabled(function (Get $get) {
                                        return !$get('product_id');
                                    })
                                    ->required(),

                                Forms\Components\TextInput::make('discount_price')
                                    ->label(__('models.transactions.fields.discount_price'))
                                    ->live()
                                    ->integer()
                                    ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                                        $product_id = $get('product_id');
                                        $price = Product::find($product_id)->purchase_price ?? 0;
                                        $subtotal = $price * $get('qty');
                                        $subtotal_after_discount = $subtotal - $state;
                                        $profit_price = $subtotal;

                                        $set('subtotal', $subtotal);
                                        $set('subtotal_after_discount', $subtotal_after_discount);
                                        $set('profit_price', $profit_price);
                                    })
                                    ->disabled(function (Get $get) {
                                        return !$get('product_id');
                                    })
                                    ->required(),

                                Forms\Components\Placeholder::make("p_subtotal")
                                    ->label(__('models.transactions.fields.subtotal'))
                                    ->hiddenLabel()
                                    ->content(function (Get $get) {
                                        $subtotal = $get('subtotal');
                                        return __("Rp. " . number_format($subtotal, 0, ',', '.'));
                                    }),
                                Forms\Components\Hidden::make('subtotal')
                                    ->disabled()
                                    ->required(),

                                Forms\Components\Placeholder::make("p_subtotal_after_discount")
                                    ->label(__('models.transactions.fields.subtotal_after_discount'))
                                    ->hiddenLabel()
                                    ->content(function (Get $get) {
                                        $subtotal_after_discount = $get('subtotal_after_discount');
                                        return __("Rp. " . number_format($subtotal_after_discount, 0, ',', '.'));
                                    }),
                                Forms\Components\Hidden::make('subtotal_after_discount')
                                    ->disabled()
                                    ->required(),

                                Forms\Components\Placeholder::make("p_profit_price")
                                    ->label(__('models.transactions.fields.profit_price'))
                                    ->hiddenLabel()
                                    ->content(function (Get $get) {
                                        $profit_price = $get('profit_price');
                                        return __("Rp. " . number_format($profit_price, 0, ',', '.'));
                                    }),
                                Forms\Components\Hidden::make('profit_price')
                                    ->disabled()
                                    ->required(),
                            ]),
                    ]),
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
                ->label(__('models.common.save'))
                ->submit('save'),
        ];
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
}
