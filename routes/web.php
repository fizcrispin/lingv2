<?php

use Illuminate\Support\Facades\Route;

Route::get('/print/{id}/{type}', function ($id, $type) {
    $record = \App\Models\InvoiceLingkungan::with('pendaftar.paket', 'pendaftar.jenisSampel')->findOrFail($id);
    
    $parameters = collect();
    $pendaftar = $record->pendaftar;

    if ($pendaftar) {
        $parameterIds = [];
        
        // Manual parameters
        if (is_array($pendaftar->parameter)) {
            $parameterIds = array_merge($parameterIds, $pendaftar->parameter);
        }

        // Package parameters
        if ($pendaftar->paket_id && $pendaftar->paket) {
            if (is_array($pendaftar->paket->parameter)) {
                $parameterIds = array_merge($parameterIds, $pendaftar->paket->parameter);
            }
        }

        $parameterIds = array_unique(array_filter(array_map('intval', $parameterIds)));
        
        if (!empty($parameterIds)) {
            $parameters = \App\Models\ParameterLingkungan::whereIn('id', $parameterIds)->get();
        }
    }

    $titles = [
        'faktur' => 'Faktur Penagihan',
        'kuitansi' => 'Kuitansi Layanan',
        'bukti_bayar' => 'Bukti Pembayaran / Kuitansi',
    ];
    
    return view('print.invoice', [
        'record' => $record,
        'title' => $titles[$type] ?? 'Dokumen Transaksi',
        'parameters' => $parameters,
    ]);
})->name('print.transaksi');

Route::get('/input-hasil/{record}/cetak', App\Http\Controllers\CetakHasilController::class)->name('cetak.hasil');
