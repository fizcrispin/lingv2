<?php

namespace App\Filament\Resources\JenisSampelResource\Pages;

use App\Filament\Resources\JenisSampelResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageJenisSampels extends ManageRecords
{
    protected static string $resource = JenisSampelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
