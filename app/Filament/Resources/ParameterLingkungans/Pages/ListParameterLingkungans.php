<?php

namespace App\Filament\Resources\ParameterLingkungans\Pages;

use App\Filament\Resources\ParameterLingkungans\ParameterLingkunganResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListParameterLingkungans extends ListRecords
{
    protected static string $resource = ParameterLingkunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
