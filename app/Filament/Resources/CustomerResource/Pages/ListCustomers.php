<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use Filament\Actions;
use App\Models\Customer;
use App\Enums\CustomerTypeEnum;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CustomerResource;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [];

        $tabs['all'] = Tab::make(trans('models.common.all'))
            ->badge(Customer::count());

        foreach (CustomerTypeEnum::cases() as $type) {
            $tabs[$type->value] = Tab::make($type->value)
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', $type->value))
                ->badge(Customer::where('type', $type->value)->count())
                ->badgeColor($type->getColor())
                ->badgeIcon($type->getIcon());
        }

        return $tabs;
    }
}
