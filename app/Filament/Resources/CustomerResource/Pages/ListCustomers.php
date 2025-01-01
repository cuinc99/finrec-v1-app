<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Enums\CustomerTypeEnum;
use App\Filament\Resources\CustomerResource;
use App\Models\Customer;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->disabled(fn (): bool => Customer::isOutOfQuota()),
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

    public function getSubheading(): ?string
    {
        return auth()->user()->role->isFree()
            ? sprintf(
                __('models.common.free_warning'),
                Customer::FREE_LIMIT,
                __('models.customers.title'),
                Customer::where('user_id', auth()->id())->count(),
                __('models.customers.title'),
            )
            : null;
    }
}
