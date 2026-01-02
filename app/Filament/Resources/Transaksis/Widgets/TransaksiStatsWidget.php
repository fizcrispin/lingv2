<?php

namespace App\Filament\Resources\Transaksis\Widgets;

use App\Models\InvoiceLingkungan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransaksiStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Belum Bayar', InvoiceLingkungan::whereIn('status_bayar', [0, 1])->count())
                ->description('Menunggu Pembayaran (0 & 1)')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
            Stat::make('Sudah Bayar', InvoiceLingkungan::where('status_bayar', 2)->count())
                ->description('Lunas (2)')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Semua Data', InvoiceLingkungan::count())
                ->description('Total Seluruh Transaksi')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }
}
