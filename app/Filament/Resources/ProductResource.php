<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\ActionSize;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-m-gift';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('models.products.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('selling_price')
                            ->label(__('models.products.fields.selling_price'))
                            ->required()
                            ->numeric(),
                        Forms\Components\Textarea::make('description')
                            ->label(__('models.products.fields.description'))
                            ->columnSpanFull(),
                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->user()->id),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('models.products.fields.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('selling_price')
                    ->label(__('models.products.fields.selling_price'))
                    ->formatStateUsing(fn (string $state): string => __("Rp. " . number_format($state, 0, ',', '.')))
                    ->sortable(),
                Tables\Columns\TextColumn::make('sold')
                    ->label(__('models.products.fields.sold'))
                    ->alignCenter(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->label(__('models.common.actions'))
                    ->button()
                    ->color(Color::Gray)
                    ->size(ActionSize::Small),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProducts::route('/'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make()
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label(__('models.products.fields.name')),
                        Infolists\Components\TextEntry::make('selling_price')
                            ->label(__('models.products.fields.selling_price'))
                            ->formatStateUsing(fn (string $state): string => __("Rp. " . number_format($state, 0, ',', '.'))),
                        Infolists\Components\TextEntry::make('sold')
                            ->label(__('models.products.fields.sold')),
                        Infolists\Components\TextEntry::make('description')
                            ->label(__('models.products.fields.description')),
                    ]),

            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getModel()::query()->latest();
    }

    public static function getLabel(): string
    {
        return __('models.products.title');
    }

    public static function canAccess(): bool
    {
        return auth()->user()->role->isUser();
    }
}
