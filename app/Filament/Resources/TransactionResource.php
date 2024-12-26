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
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-m-shopping-cart';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        $warning = auth()->user()->role->isFree()
            ? 'Free users can only create ' . Transaction::FREE_LIMIT . ' transactions. You have created ' . Transaction::where('user_id', auth()->id())->count() . ' transactions.'
            : null;

        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->description($warning)
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
            ]);
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
                Header::make('discount')
                    ->label(__('models.transactions.fields.discount'))
                    ->width('150px'),
                Header::make('subtotal_after_discount')
                    ->label(__('models.transactions.fields.subtotal_after_discount')),
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
                        $discount = $get('discount');

                        static::updateData($product, $quantity, $discount, $set);
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
                        $discount = $get('discount');

                        static::updateData($product, $quantity, $discount, $set);
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
                        $discount = $state;

                        static::updateData($product, $quantity, $discount, $set);
                    })
                    ->disabled(fn(Get $get) => !$get('product_id')),

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
            ]);
    }

    public static function updateData($product, $quantity, $discount, Set $set)
    {
        $price = $product?->selling_price ?? 0;
        $subtotal = $price * $quantity;
        $subtotalAfterDiscount = $subtotal - $discount;

        $set('price', $price);
        $set('subtotal', $subtotal);
        $set('subtotal_after_discount', $subtotalAfterDiscount);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                ExportAction::make()->exports([
                    ExcelExport::make('table')
                        ->withFilename(date('Y-m-d') . ' - export_' . __('models.transactions.title'))
                        ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                        ->fromTable()
                        ->withColumns([
                            Column::make('purchase_date')->heading(__('models.transactions.fields.purchase_date')),
                            Column::make('customer.name')->heading(__('models.customers.fields.name')),
                            Column::make('product.name')->heading(__('models.products.fields.name')),
                            Column::make('price')->heading(__('models.transactions.fields.price')),
                            Column::make('quantity')->heading(__('models.transactions.fields.quantity')),
                            Column::make('discount')->heading(__('models.transactions.fields.discount')),
                            Column::make('subtotal')->heading(__('models.transactions.fields.subtotal')),
                            Column::make('subtotal_after_discount')->heading(__('models.transactions.fields.subtotal_after_discount')),
                        ]),
                ])->label('Export XLS'),
            ])
            ->columns([
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
                Tables\Columns\TextColumn::make('discount')
                    ->label(__('models.transactions.fields.discount'))
                    ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn(string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                            ->label('Total ' . __('models.transactions.fields.discount')),
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
        return auth()->user()->role->isUser() || auth()->user()->role->isFree();
    }

    public static function canCreate(): bool
    {
        return !static::getModel()::isOutOfQuota();
    }
}
