<?php

namespace App\Filament\Resources\PaketParameterResource\Pages;

use App\Filament\Resources\PaketParameterResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePaketParameters extends ManageRecords
{
    protected static string $resource = PaketParameterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
