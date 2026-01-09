<?php

namespace App\Filament\Resources\InputHasilResource\Pages;

use App\Filament\Resources\InputHasilResource;
use App\Models\PendaftarLingkungan;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;

class ListInputHasils extends ListRecords
{
    protected static string $resource = InputHasilResource::class;

    public function getTitle(): string
    {
        return 'Hasil Laboratorium Lingkungan';
    }

    public function getTabs(): array
    {
        return [
            'belum_selesai' => Tab::make('Belum Selesai')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('hasilLingkungans', fn ($sq) => $sq->whereNull('hasil_parameter')))
                ->icon('heroicon-m-clock')
                ->badge(fn () => $this->getFilteredCount(
                    PendaftarLingkungan::whereHas('hasilLingkungans', fn ($sq) => $sq->whereNull('hasil_parameter'))
                ))
                ->badgeColor('danger'),
            'sudah_selesai' => Tab::make('Sudah Selesai')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDoesntHave('hasilLingkungans', fn ($q) => $q->whereNull('hasil_parameter')))
                ->icon('heroicon-m-check-circle')
                ->badge(fn () => $this->getFilteredCount(
                    PendaftarLingkungan::whereHas('hasilLingkungans')->whereDoesntHave('hasilLingkungans', fn ($q) => $q->whereNull('hasil_parameter'))
                ))
                ->badgeColor('success'),
            'dicetak' => Tab::make('Dicetak')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('statusData', fn ($q) => $q->where('dicetak', true)))
                ->icon('heroicon-m-printer')
                ->badge(fn () => $this->getFilteredCount(
                    PendaftarLingkungan::whereHas('statusData', fn ($q) => $q->where('dicetak', true))
                ))
                ->badgeColor('info'),
            'notif' => Tab::make('Sudah Notif')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('statusData', fn ($q) => $q->where('notifikasi', '1')))
                ->icon('heroicon-m-chat-bubble-left')
                ->badge(fn () => $this->getFilteredCount(
                    PendaftarLingkungan::whereHas('statusData', fn ($q) => $q->where('notifikasi', '1'))
                ))
                ->badgeColor('primary'),
            'diambil' => Tab::make('Sudah Diambil')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('statusData', fn ($q) => $q->where('diambil', true)))
                ->icon('heroicon-m-hand-thumb-up')
                ->badge(fn () => $this->getFilteredCount(
                    PendaftarLingkungan::whereHas('statusData', fn ($q) => $q->where('diambil', true))
                ))
                ->badgeColor('success'),
            'all' => Tab::make('Semua Data')
                ->badge(fn () => $this->getFilteredCount(PendaftarLingkungan::query()))
                ->badgeColor('gray'),
        ];
    }

    private function getFilteredCount(Builder $query): int
    {
        $dateData = $this->tableFilters['tanggal_pendaftar'] ?? [];
        $start = $dateData['dari_tanggal'] ?? null;
        $end = $dateData['sampai_tanggal'] ?? null;

        $jenisSampelData = $this->tableFilters['jenis_sampling'] ?? [];
        $jenisSampelId = $jenisSampelData['value'] ?? null;

        return $query
            ->when($start, fn ($q) => $q->whereDate('tanggal_pendaftar', '>=', $start))
            ->when($end, fn ($q) => $q->whereDate('tanggal_pendaftar', '<=', $end))
            ->when($jenisSampelId, fn ($q) => $q->where('jenis_sampling', $jenisSampelId))
            ->count();
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'belum_selesai';
    }
}
