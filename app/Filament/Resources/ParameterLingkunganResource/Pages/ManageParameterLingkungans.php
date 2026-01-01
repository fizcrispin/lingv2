<?php

namespace App\Filament\Resources\ParameterLingkunganResource\Pages;

use App\Filament\Resources\ParameterLingkunganResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageParameterLingkungans extends ManageRecords
{
    protected static string $resource = ParameterLingkunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
