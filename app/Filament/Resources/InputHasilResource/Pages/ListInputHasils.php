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

    public function getTabs(): array
    {
        return [
            'belum_selesai' => Tab::make('Hasil Belum Selesai')
                ->modifyQueryUsing(fn (Builder $query) => $query->where(function ($q) {
                    $q->whereDoesntHave('hasilLingkungans')
                      ->orWhereHas('hasilLingkungans', fn ($sq) => $sq->whereNull('hasil_parameter'));
                }))
                ->icon('heroicon-m-clock')
                ->badge(fn () => PendaftarLingkungan::where(function ($q) {
                    $q->whereDoesntHave('hasilLingkungans')
                      ->orWhereHas('hasilLingkungans', fn ($sq) => $sq->whereNull('hasil_parameter'));
                })->count())
                ->badgeColor('warning'),
            'sudah_selesai' => Tab::make('Hasil Sudah Selesai')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('hasilLingkungans')->whereDoesntHave('hasilLingkungans', fn ($q) => $q->whereNull('hasil_parameter')))
                ->icon('heroicon-m-check-circle')
                ->badge(fn () => PendaftarLingkungan::whereHas('hasilLingkungans')->whereDoesntHave('hasilLingkungans', fn ($q) => $q->whereNull('hasil_parameter'))->count())
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
