<?php

namespace App\Filament\Resources;

use App\Enums\CustomerTypeEnum;
use App\Filament\Resources\CustomerResource\RelationManagers\TransactionsRelationManager;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\Widgets\TransactionStats;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-m-shopping-cart';

    public static function form(Form $form): Form
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
                            ->relationship(name: 'customer', titleAttribute: 'name')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('models.customers.fields.name'))
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\ToggleButtons::make('type')
                                    ->label(__('models.customers.fields.type'))
                                    ->required()
                                    ->inline()
                                    ->options(CustomerTypeEnum::class),
                                Forms\Components\Hidden::make('user_id')
                                    ->default(auth()->user()->id),
                            ])
                            ->options(Customer::pluck('name', 'id'))
                            ->prefixIcon('heroicon-m-user-circle')
                            ->searchable()
                            ->required()
                            ->hiddenOn(TransactionsRelationManager::class),
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
                    ->width('150px')
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
                    ->suffix('Item')
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
                    ->prefix('Rp')
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
                    ->prefix('Rp')
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('is_paid')
                    ->label(__('models.transactions.fields.is_paid'))
                    ->formatStateUsing(fn(bool $state) => $state ? __('models.transactions.fields.is_paid_options.paid') : __('models.transactions.fields.is_paid_options.unpaid'))
                    ->badge()
                    ->color(fn(bool $state) => match ($state) {
                        true => Color::Sky,
                        false => Color::Red,
                    }),
                Tables\Columns\TextColumn::make('purchase_date')
                    ->label(__('models.transactions.fields.purchase_date'))
                    ->date()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label(__('models.transactions.fields.customer'))
                    ->hiddenOn(TransactionsRelationManager::class),
                Tables\Columns\TextColumn::make('customer.type')
                    ->label(__('models.customers.fields.type'))
                    ->badge()
                    ->hiddenOn(TransactionsRelationManager::class),
                Tables\Columns\TextColumn::make('product.name')
                    ->label(__('models.transactions.fields.product'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label(__('models.transactions.fields.price'))
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.'))),
                Tables\Columns\TextColumn::make('quantity')
                    ->label(__('models.transactions.fields.quantity'))
                    ->alignCenter()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total ' . __('models.transactions.fields.quantity')),
                    ]),
                Tables\Columns\TextColumn::make('discount_per_item')
                    ->label(__('models.transactions.fields.discount_per_item'))
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.'))),
                Tables\Columns\TextColumn::make('total_discount_per_item')
                    ->label(__('models.transactions.fields.total_discount_per_item'))
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                            ->label(__('models.transactions.fields.total_discount_per_item')),
                    ]),
                Tables\Columns\TextColumn::make('discount')
                    ->label(__('models.transactions.fields.discount'))
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                            ->label('Total ' . __('models.transactions.fields.discount')),
                    ]),
                Tables\Columns\TextColumn::make('total_discount')
                    ->label(__('models.transactions.fields.total_discount'))
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                            ->label(__('models.transactions.fields.total_discount')),
                    ]),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label(__('models.transactions.fields.subtotal'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                            ->label('Total ' . __('models.transactions.fields.subtotal')),
                    ]),
                Tables\Columns\TextColumn::make('subtotal_after_discount')
                    ->label(__('models.transactions.fields.subtotal_after_discount'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                    ->weight('bold')
                    ->color(Color::Blue)
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                            ->label('Total ' . __('models.transactions.fields.subtotal_after_discount')),
                    ]),
                Tables\Columns\TextColumn::make('capital')
                    ->label(__('models.transactions.fields.capital'))
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                    ->color(Color::Red)
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                            ->label(__('models.transactions.fields.capital')),
                    ]),
                Tables\Columns\TextColumn::make('profit')
                    ->label(__('models.transactions.fields.profit'))
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                    ->searchable()
                    ->sortable()
                    ->color(Color::Teal)
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                            ->label('Total ' . __('models.transactions.fields.profit')),
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label(__('models.transactions.fields.product'))
                    ->options(auth()->user()->products()->pluck('name', 'id'))
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('customer_id')
                    ->label(__('models.transactions.fields.customer'))
                    ->options(auth()->user()->customers()->pluck('name', 'id'))
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->hiddenOn(TransactionsRelationManager::class),
                Tables\Filters\SelectFilter::make('customer_type')
                    ->label(__('models.customers.fields.type') . ' ' . __('models.transactions.fields.customer'))
                    ->options(CustomerTypeEnum::class)
                    ->searchable()
                    ->modifyQueryUsing(function ($query, $data) {
                        if (!empty($data['value'])) {
                            $query->whereHas('customer', function ($query) use ($data) {
                                $query->where('type', $data);
                            });
                        }
                    })
                    ->hiddenOn(TransactionsRelationManager::class),
                Tables\Filters\Filter::make('purchase_date')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('models.common.created_from'))
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->prefixIcon('heroicon-m-calendar-days'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('models.common.created_until'))
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->prefixIcon('heroicon-m-calendar-days'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('purchase_date', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('purchase_date', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = __('models.common.created_from') . ' ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = __('models.common.created_until') . ' ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }

                        return $indicators;
                    }),
            ], layout: FiltersLayout::Modal)
            ->deferFilters()
            ->persistFiltersInSession()
            ->filtersTriggerAction(
                fn(Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('Filter'),
            )
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

    public static function getWidgets(): array
    {
        return [
            TransactionStats::class,
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
        return static::getModel()::query()->latest('purchase_date');
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
