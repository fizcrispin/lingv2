<?php

namespace App\Filament\Resources\PendaftarLingkungans\Pages;

use App\Filament\Resources\PendaftarLingkungans\PendaftarLingkunganResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPendaftarLingkungan extends EditRecord
{
    protected static string $resource = PendaftarLingkunganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
