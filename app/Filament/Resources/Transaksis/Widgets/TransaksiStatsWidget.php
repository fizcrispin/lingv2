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
            Stat::make('Total Tagihan', InvoiceLingkungan::count())
                ->description('Total seluruh data tagihan')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
            Stat::make('Sudah Lunas', InvoiceLingkungan::where('status_bayar', 2)->count())
                ->description('Transaksi yang sudah dibayar')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Belum Lunas', InvoiceLingkungan::where('status_bayar', '<', 2)->count())
                ->description('Transaksi menunggu pembayaran')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
        ];
    }
}
