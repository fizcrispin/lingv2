<?php

namespace App\Filament\Resources\Transaksis\Pages;

use App\Filament\Resources\Transaksis\TransaksiResource;
use App\Filament\Resources\Transaksis\Widgets\TransaksiStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransaksis extends ListRecords
{
    protected static string $resource = TransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Tagihan Baru'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TransaksiStatsWidget::class,
        ];
    }
}
