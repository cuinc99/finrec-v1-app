<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use Filament\Actions;
use Filament\Support\Enums\MaxWidth;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\CustomerResource;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public static function getNavigationLabel(): string
    {
        return __('models.common.edit'). ' ' . __('models.customers.title');
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }
}
