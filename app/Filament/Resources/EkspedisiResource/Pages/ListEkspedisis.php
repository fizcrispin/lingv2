<?php

namespace App\Filament\Resources\EkspedisiResource\Pages;

use App\Filament\Resources\EkspedisiResource;
use App\Models\Ekspedisi;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;

class ListEkspedisis extends ListRecords
{
    protected static string $resource = EkspedisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua Data')
                ->badge(fn () => Ekspedisi::count()),
            'belum_diterima' => Tab::make('Belum Diterima')
                ->modifyQueryUsing(fn ($query) => $query->where('sampel_diterima', false))
                ->badge(fn () => Ekspedisi::where('sampel_diterima', false)->count())
                ->badgeColor('danger'),
            'diterima' => Tab::make('Diterima')
                ->modifyQueryUsing(fn ($query) => $query->where('sampel_diterima', true))
                ->badge(fn () => Ekspedisi::where('sampel_diterima', true)->count())
                ->badgeColor('info'),
            'selesai_input' => Tab::make('Selesai Input')
                ->modifyQueryUsing(fn ($query) => $query->whereHas('pendaftarLingkungan', function ($q) {
                    $q->whereHas('hasilLingkungans')
                      ->whereDoesntHave('hasilLingkungans', function ($q2) {
                          $q2->whereNull('hasil_parameter')->orWhere('hasil_parameter', '');
                      });
                }))
                ->badge(fn () => Ekspedisi::whereHas('pendaftarLingkungan', function ($q) {
                    $q->whereHas('hasilLingkungans')
                      ->whereDoesntHave('hasilLingkungans', function ($q2) {
                          $q2->whereNull('hasil_parameter')->orWhere('hasil_parameter', '');
                      });
                })->count())
                ->badgeColor('success')
                ->icon('heroicon-m-check-circle'),
            'verif_hasil' => Tab::make('Verif Hasil')
                ->modifyQueryUsing(fn ($query) => $query->where('verifikasi_hasil', true))
                ->badge(fn () => Ekspedisi::where('verifikasi_hasil', true)->count())
                ->badgeColor('primary'),
            'valid_1' => Tab::make('Valid 1')
                ->modifyQueryUsing(fn ($query) => $query->where('validasi1', true))
                ->badge(fn () => Ekspedisi::where('validasi1', true)->count())
                ->badgeColor('success'),
            'valid_2' => Tab::make('Valid 2')
                ->modifyQueryUsing(fn ($query) => $query->where('validasi2', true))
                ->badge(fn () => Ekspedisi::where('validasi2', true)->count())
                ->badgeColor('success'),
            'selesai' => Tab::make('Selesai')
                ->modifyQueryUsing(fn ($query) => $query
                    ->where('sampel_diterima', true)
                    ->where('verifikasi_hasil', true)
                    ->where('validasi1', true)
                    ->where('validasi2', true))
                ->badge(fn () => Ekspedisi::where('sampel_diterima', true)
                    ->where('verifikasi_hasil', true)
                    ->where('validasi1', true)
                    ->where('validasi2', true)->count())
                ->badgeColor('success')
                ->icon('heroicon-m-check-badge'),
            'dimusnahkan' => Tab::make('Dimusnahkan')
                ->modifyQueryUsing(fn ($query) => $query->where('sampel_dimusnahkan', true))
                ->badge(fn () => Ekspedisi::where('sampel_dimusnahkan', true)->count())
                ->badgeColor('warning')
                ->icon('heroicon-m-trash'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'belum_diterima';
    }
}
