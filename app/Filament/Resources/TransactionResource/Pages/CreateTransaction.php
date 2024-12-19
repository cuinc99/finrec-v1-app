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
use Awcodes\TableRepeater\Header;
use Filament\Resources\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Forms\Components\Actions\Action;
use App\Filament\Resources\TransactionResource;
use Awcodes\TableRepeater\Components\TableRepeater;

class CreateTransaction extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string $resource = TransactionResource::class;

    protected static string $view = 'filament.resources.transaction-resource.pages.create-transaction';

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
                            ->default(now())
                            ->maxDate(now()->addDay())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->prefixIcon('heroicon-m-calendar-days')
                            ->required(),
                        Forms\Components\Select::make('customer_id')
                            ->label(__('models.customers.title'))
                            ->options(Customer::pluck('name', 'id'))
                            ->prefixIcon('heroicon-m-user-circle')
                            ->searchable()
                            ->required(),
                    ])->columns(2),
                Forms\Components\Section::make(__('models.transactions.title'))
                    ->headerActions([
                        Action::make('reset')
                            ->modalHeading(__('models.common.reset_action_heading'))
                            ->modalDescription('__(models.common.reset_action_description).')
                            ->requiresConfirmation()
                            ->color('danger')
                            ->action(fn(Forms\Set $set) => $set('items', [])),
                    ])
                    ->schema([
                        static::getItemsRepeater(),
                    ]),
            ])->statePath('data');
    }

    public static function getItemsRepeater(): TableRepeater
    {
        return TableRepeater::make('items')
            ->hiddenLabel()
            ->columnSpanFull()
            ->headers([
                Header::make('product')
                    ->label(__('models.transactions.fields.product'))
                    ->markAsRequired(),
                Header::make('price')
                    ->label(__('models.transactions.fields.price')),
                Header::make('quantity')
                    ->label(__('models.transactions.fields.quantity'))
                    ->width('100px')
                    ->markAsRequired(),
                Header::make('subtotal')
                    ->label(__('models.transactions.fields.subtotal')),
                Header::make('discount_per_item')
                    ->label(__('models.transactions.fields.discount_per_item'))
                    ->width('150px'),
                Header::make('total_discount_per_item')
                    ->label(__('models.transactions.fields.total_discount_per_item'))
                    ->width('150px'),
                Header::make('discount')
                    ->label(__('models.transactions.fields.discount'))
                    ->width('150px'),
                Header::make('total_discount')
                    ->label(__('models.transactions.fields.total_discount'))
                    ->width('150px'),
                Header::make('subtotal_after_discount')
                    ->label(__('models.transactions.fields.subtotal_after_discount')),
                Header::make('capital')
                    ->label(__('models.transactions.fields.capital')),
                Header::make('profit')
                    ->label(__('models.transactions.fields.profit')),
                Header::make('is_paid_question')
                    ->label(__('models.transactions.fields.is_paid_question')),
            ])
            ->schema([
                // product select
                Forms\Components\Select::make('product_id')
                    ->label(__('models.transactions.fields.product'))
                    ->options(Product::pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->live(debounce: 1000)
                    ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                        $product = Product::find($state);
                        $quantity = $get('quantity');
                        $discountPerItem = $get('discount_per_item');
                        $discount = $get('discount');

                        static::updateData($product, $quantity, $discountPerItem, $discount, $set);
                    })
                    ->required(),

                // price display
                Forms\Components\Placeholder::make("price_display")
                    ->label(__('models.transactions.fields.price'))
                    ->hiddenLabel()
                    ->content(function (Get $get): string {
                        $price = $get('price');
                        return __("Rp. " . number_format($price, 0, ',', '.'));
                    }),

                // price hidden
                Forms\Components\Hidden::make('price')
                    ->required(),

                // quantity input
                Forms\Components\TextInput::make('quantity')
                    ->label(__('models.transactions.fields.quantity'))
                    ->minValue(1)
                    ->default(1)
                    ->live(debounce: 1000)
                    ->integer()
                    ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                        $product = Product::find($get('product_id'));
                        $quantity = $state;
                        $discountPerItem = $get('discount_per_item');
                        $discount = $get('discount');

                        static::updateData($product, $quantity, $discountPerItem, $discount, $set);
                    })
                    ->disabled(fn(Get $get) => !$get('product_id'))
                    ->required(),

                // subtotal display
                Forms\Components\Placeholder::make("subtotal_display")
                    ->label(__('models.transactions.fields.subtotal'))
                    ->hiddenLabel()
                    ->content(function (Get $get) {
                        $subtotal = $get('subtotal');
                        return __("Rp. " . number_format($subtotal, 0, ',', '.'));
                    }),

                // subtotal hideen
                Forms\Components\Hidden::make('subtotal')
                    ->required(),

                // discount_per_item input
                Forms\Components\TextInput::make('discount_per_item')
                    ->label(__('models.transactions.fields.discount_per_item'))
                    ->live(debounce: 1000)
                    ->minValue(0)
                    ->default(0)
                    ->integer()
                    ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                        $product = Product::find($get('product_id'));
                        $quantity = $get('quantity');
                        $discountPerItem = $state;
                        $discount = $get('discount');

                        static::updateData($product, $quantity, $discountPerItem, $discount, $set);
                    })
                    ->disabled(fn(Get $get) => !$get('product_id')),

                // total_discount_per_item display
                Forms\Components\Placeholder::make("total_discount_per_item_display")
                    ->label(__('models.transactions.fields.total_discount_per_item'))
                    ->hiddenLabel()
                    ->content(function (Get $get) {
                        $totalDiscountPerItem = $get('total_discount_per_item');
                        return __("Rp. " . number_format($totalDiscountPerItem, 0, ',', '.'));
                    }),

                // discount input
                Forms\Components\TextInput::make('discount')
                    ->label(__('models.transactions.fields.discount'))
                    ->live(debounce: 1000)
                    ->minValue(0)
                    ->default(0)
                    ->integer()
                    ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                        $product = Product::find($get('product_id'));
                        $quantity = $get('quantity');
                        $discountPerItem = $get('discount_per_item');
                        $discount = $state;

                        static::updateData($product, $quantity, $discountPerItem, $discount, $set);
                    })
                    ->disabled(fn(Get $get) => !$get('product_id')),

                // total_discount display
                Forms\Components\Placeholder::make("total_discount_display")
                    ->label(__('models.transactions.fields.total_discount'))
                    ->hiddenLabel()
                    ->content(function (Get $get) {
                        $totalDiscount = $get('total_discount');
                        return __("Rp. " . number_format($totalDiscount, 0, ',', '.'));
                    }),

                // subtotal_after_discount display
                Forms\Components\Placeholder::make("subtotal_after_discount_display")
                    ->label(__('models.transactions.fields.subtotal_after_discount'))
                    ->hiddenLabel()
                    ->content(function (Get $get) {
                        $subtotalAfterDiscount = $get('subtotal_after_discount');
                        return __("Rp. " . number_format($subtotalAfterDiscount, 0, ',', '.'));
                    }),

                // subtotal_after_discount hidden
                Forms\Components\Hidden::make('subtotal_after_discount')
                    ->required(),

                // capital_per_item hidden
                Forms\Components\Hidden::make('capital_per_item')
                    ->required(),

                // capital display
                Forms\Components\Placeholder::make("capital_display")
                    ->label(__('models.transactions.fields.capital'))
                    ->hiddenLabel()
                    ->content(function (Get $get) {
                        $profitPerItem = $get('capital');
                        return __("Rp. " . number_format($profitPerItem, 0, ',', '.'));
                    }),

                // capital hidden
                Forms\Components\Hidden::make('capital')
                    ->required(),

                // profit_per_item hidden
                Forms\Components\Hidden::make('profit_per_item')
                    ->required(),

                // profit display
                Forms\Components\Placeholder::make("profit_display")
                    ->label(__('models.transactions.fields.profit'))
                    ->hiddenLabel()
                    ->content(function (Get $get) {
                        $profit = $get('profit');
                        return __("Rp. " . number_format($profit, 0, ',', '.'));
                    }),

                // profit hidden
                Forms\Components\Hidden::make('profit')
                    ->required(),

                // is_paid hidden
                Forms\Components\Toggle::make('is_paid')
                    ->label(__('models.transactions.fields.is_paid_question'))
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark')
                    ->onColor(Color::Sky)
                    ->offColor(Color::Red),
            ]);
    }

    public static function updateData($product, $quantity, $discountPerItem, $discount, Set $set)
    {
        $price = $product?->selling_price ?? 0;
        $subtotal = $price * $quantity;
        $totalDiscountPerItem = $discountPerItem * $quantity;
        $totalDiscount = $totalDiscountPerItem + $discount;
        $subtotalAfterDiscount = $subtotal - $totalDiscount;
        $capitalPerItem = $product?->purchase_price ?? 0;
        $capital = $product?->purchase_price * $quantity;
        $profitPerItem = $price - $capitalPerItem;
        $profit = $subtotalAfterDiscount - $capital;

        $set('price', $price);
        $set('subtotal', $subtotal);
        $set('subtotal_after_discount', $subtotalAfterDiscount);
        $set('total_discount_per_item', $totalDiscountPerItem);
        $set('total_discount', $totalDiscount);
        $set('capital_per_item', $capitalPerItem);
        $set('capital', $capital);
        $set('profit_per_item', $profitPerItem);
        $set('profit', $profit);
    }

    public function save()
    {
        try {
            $transactionCode = Str::random(2) . rand(10, 99) . Str::random(2) . rand(10, 99);
            $transactions = $this->form->getState();

            foreach ($transactions['items'] as $transaction) {
                Transaction::create([
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
            }

            Notification::make()
                ->success()
                ->title(__('models.transactions.title') . ' berhasil di ' . __('models.common.create'))
                ->send();

            return redirect($this->getResource()::getUrl('index'));
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
            Action::make('cancel')
                ->label(__('models.common.cancel'))
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public function getTitle(): string | Htmlable
    {
        if (filled(static::$title)) {
            return static::$title;
        }

        return __('models.common.create') . ' ' . __('models.transactions.title');
    }
}
