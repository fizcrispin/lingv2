<?php

namespace App\Filament\Resources\PendaftarLingkungans\Pages;

use App\Filament\Resources\PendaftarLingkungans\PendaftarLingkunganResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPendaftarLingkungans extends ListRecords
{
    protected static string $resource = PendaftarLingkunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
