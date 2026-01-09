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
                ->badge(fn () => $this->getFilteredCount(Ekspedisi::query())),
            'belum_diterima' => Tab::make('Belum Diterima')
                ->modifyQueryUsing(fn ($query) => $query->where('sampel_diterima', false))
                ->badge(fn () => $this->getFilteredCount(Ekspedisi::where('sampel_diterima', false)))
                ->badgeColor('danger'),
            'diterima' => Tab::make('Diterima')
                ->modifyQueryUsing(fn ($query) => $query->where('sampel_diterima', true))
                ->badge(fn () => $this->getFilteredCount(Ekspedisi::where('sampel_diterima', true)))
                ->badgeColor('info'),
            'selesai_input' => Tab::make('Selesai Input')
                ->modifyQueryUsing(fn ($query) => $query->whereHas('pendaftarLingkungan', function ($q) {
                    $q->whereHas('hasilLingkungans')
                      ->whereDoesntHave('hasilLingkungans', function ($q2) {
                          $q2->whereNull('hasil_parameter')->orWhere('hasil_parameter', '');
                      });
                }))
                ->badge(fn () => $this->getFilteredCount(Ekspedisi::whereHas('pendaftarLingkungan', function ($q) {
                    $q->whereHas('hasilLingkungans')
                      ->whereDoesntHave('hasilLingkungans', function ($q2) {
                          $q2->whereNull('hasil_parameter')->orWhere('hasil_parameter', '');
                      });
                })))
                ->badgeColor('success')
                ->icon('heroicon-m-check-circle'),
            'verif_hasil' => Tab::make('Verif Hasil')
                ->modifyQueryUsing(fn ($query) => $query->where('verifikasi_hasil', true))
                ->badge(fn () => $this->getFilteredCount(Ekspedisi::where('verifikasi_hasil', true)))
                ->badgeColor('primary'),
            'valid_1' => Tab::make('Valid 1')
                ->modifyQueryUsing(fn ($query) => $query->where('validasi1', true))
                ->badge(fn () => $this->getFilteredCount(Ekspedisi::where('validasi1', true)))
                ->badgeColor('success'),
            'valid_2' => Tab::make('Valid 2')
                ->modifyQueryUsing(fn ($query) => $query->where('validasi2', true))
                ->badge(fn () => $this->getFilteredCount(Ekspedisi::where('validasi2', true)))
                ->badgeColor('success'),
            'selesai' => Tab::make('Selesai')
                ->modifyQueryUsing(fn ($query) => $query
                    ->where('sampel_diterima', true)
                    ->where('verifikasi_hasil', true)
                    ->where('validasi1', true)
                    ->where('validasi2', true))
                ->badge(fn () => $this->getFilteredCount(Ekspedisi::where('sampel_diterima', true)
                    ->where('verifikasi_hasil', true)
                    ->where('validasi1', true)
                    ->where('validasi2', true)))
                ->badgeColor('success')
                ->icon('heroicon-m-check-badge'),
            'dimusnahkan' => Tab::make('Dimusnahkan')
                ->modifyQueryUsing(fn ($query) => $query->where('sampel_dimusnahkan', true))
                ->badge(fn () => $this->getFilteredCount(Ekspedisi::where('sampel_dimusnahkan', true)))
                ->badgeColor('warning')
                ->icon('heroicon-m-trash'),
        ];
    }

    private function getFilteredCount(\Illuminate\Database\Eloquent\Builder $query): int
    {
        $data = $this->tableFilters['tanggal_pendaftar'] ?? [];
        $start = $data['dari_tanggal'] ?? null;
        $end = $data['sampai_tanggal'] ?? null;

        return $query
            ->when($start || $end, function($q) use ($start, $end) {
                 return $q->whereHas('pendaftarLingkungan', function($sq) use ($start, $end) {
                      $sq->when($start, fn($ssq) => $ssq->whereDate('tanggal_pendaftar', '>=', $start))
                         ->when($end, fn($ssq) => $ssq->whereDate('tanggal_pendaftar', '<=', $end));
                 });
            })
            ->count();
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'belum_diterima';
    }
}
