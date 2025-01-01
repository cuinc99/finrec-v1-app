<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Enums\MaxWidth;

class Dashboard extends BaseDashboard
{
    use BaseDashboard\Concerns\HasFiltersForm;

    public function filtersForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->label(__('models.transactions.fields.product'))
                            ->options(auth()->user()->products()->pluck('name', 'id'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-m-gift'),
                        Forms\Components\Select::make('customer_id')
                            ->label(__('models.transactions.fields.customer'))
                            ->options(auth()->user()->customers()->pluck('name', 'id'))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-m-users'),
                        Forms\Components\DatePicker::make('created_from')
                            ->label(__('models.common.created_from'))
                            ->maxDate(fn (Get $get) => $get('created_until') ?: now())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->prefixIcon('heroicon-m-calendar-days'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label(__('models.common.created_until'))
                            ->minDate(fn (Get $get) => $get('created_from') ?: now())
                            ->maxDate(now())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->prefixIcon('heroicon-m-calendar-days'),
                    ])
                    ->columns(4),
            ]);
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
}
