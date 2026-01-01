<?php

namespace App\Filament\Widgets;

use App\Models\PendaftarLingkungan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use NumberFormatter;

class LabStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRegistrations = PendaftarLingkungan::count();
        
        $totalRevenue = PendaftarLingkungan::all()->sum(function ($record) {
            return $record->total_harga;
        });

        $formatter = new NumberFormatter('id_ID', NumberFormatter::CURRENCY);
        $formattedRevenue = $formatter->formatCurrency($totalRevenue, 'IDR');

        return [
            Stat::make('Total Registrasi', $totalRegistrations)
                ->description('Seluruh pendaftaran laboratorium')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('info'),
            Stat::make('Estimasi Pendapatan', $formattedRevenue)
                ->description('Total dari parameter & paket')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Rata-rata Biaya', $totalRegistrations > 0 ? $formatter->formatCurrency($totalRevenue / $totalRegistrations, 'IDR') : 'IDR 0')
                ->description('Biaya rata-rata per sampel')
                ->descriptionIcon('heroicon-m-presentation-chart-line')
                ->color('primary'),
        ];
    }
}
