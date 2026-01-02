<?php

namespace App\Filament\Resources\Transaksis\Pages;

use App\Filament\Resources\Transaksis\TransaksiResource;
use App\Filament\Resources\Transaksis\Widgets\TransaksiStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

use Filament\Schemas\Components\Tabs\Tab;

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

    public function getTabs(): array
    {
        return [
            'belum_bayar' => Tab::make('Belum Bayar')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status_bayar', [0, 1]))
                ->badge(\App\Models\InvoiceLingkungan::whereIn('status_bayar', [0, 1])->count())
                ->badgeColor('danger'),
            'sudah_bayar' => Tab::make('Sudah Bayar')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status_bayar', 2))
                ->badge(\App\Models\InvoiceLingkungan::where('status_bayar', 2)->count())
                ->badgeColor('success'),
            'all' => Tab::make('Semua Data')
                ->badge(\App\Models\InvoiceLingkungan::count())
                ->badgeColor('gray'),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'belum_bayar';
    }
}
