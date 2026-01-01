<?php

namespace App\Filament\Resources\ParameterLingkungans\Pages;

use App\Filament\Resources\ParameterLingkungans\ParameterLingkunganResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditParameterLingkungan extends EditRecord
{
    protected static string $resource = ParameterLingkunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
