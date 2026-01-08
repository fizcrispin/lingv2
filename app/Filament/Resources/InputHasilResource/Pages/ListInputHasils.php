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
                ->badge(fn () => PendaftarLingkungan::whereHas('hasilLingkungans', fn ($sq) => $sq->whereNull('hasil_parameter'))->count())
                ->badgeColor('danger'),
            'sudah_selesai' => Tab::make('Sudah Selesai')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDoesntHave('hasilLingkungans', fn ($q) => $q->whereNull('hasil_parameter')))
                ->icon('heroicon-m-check-circle')
                ->badge(fn () => PendaftarLingkungan::whereHas('hasilLingkungans')->whereDoesntHave('hasilLingkungans', fn ($q) => $q->whereNull('hasil_parameter'))->count())
                ->badgeColor('success'),
            'dicetak' => Tab::make('Dicetak')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('statusData', fn ($q) => $q->where('dicetak', true)))
                ->icon('heroicon-m-printer')
                ->badge(fn () => PendaftarLingkungan::whereHas('statusData', fn ($q) => $q->where('dicetak', true))->count())
                ->badgeColor('info'),
            'notif' => Tab::make('Sudah Notif')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('statusData', fn ($q) => $q->where('notifikasi', '1')))
                ->icon('heroicon-m-chat-bubble-left')
                ->badge(fn () => PendaftarLingkungan::whereHas('statusData', fn ($q) => $q->where('notifikasi', '1'))->count())
                ->badgeColor('primary'),
            'diambil' => Tab::make('Sudah Diambil')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('statusData', fn ($q) => $q->where('diambil', true)))
                ->icon('heroicon-m-hand-thumb-up')
                ->badge(fn () => PendaftarLingkungan::whereHas('statusData', fn ($q) => $q->where('diambil', true))->count())
                ->badgeColor('success'),
            'all' => Tab::make('Semua Data')
                ->badge(PendaftarLingkungan::count())
                ->badgeColor('gray'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'belum_selesai';
    }
}
