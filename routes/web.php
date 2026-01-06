<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

Route::middleware('auth')->group(function () {
    Route::get('/print/label/{id}', function ($id) {
        $record = \App\Models\PendaftarLingkungan::with('jenisSampel')->findOrFail($id);
        return view('print.label', compact('record'));
    })->name('print.label');

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
        
        $terbilang = function($x) use (&$terbilang) {
            $angka = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
            if ($x < 12) return " " . $angka[$x];
            elseif ($x < 20) return $terbilang($x - 10) . " belas";
            elseif ($x < 100) return $terbilang($x / 10) . " puluh" . $terbilang($x % 10);
            elseif ($x < 200) return " seratus" . $terbilang($x - 100);
            elseif ($x < 1000) return $terbilang($x / 100) . " ratus" . $terbilang($x % 100);
            elseif ($x < 2000) return " seribu" . $terbilang($x - 1000);
            elseif ($x < 1000000) return $terbilang($x / 1000) . " ribu" . $terbilang($x % 1000);
            elseif ($x < 1000000000) return $terbilang($x / 1000000) . " juta" . $terbilang($x % 1000000);
        };

        return view('print.invoice', [
            'record' => $record,
            'title' => $titles[$type] ?? 'Dokumen Transaksi',
            'type' => $type, // Pass strict type key
            'parameters' => $parameters,
            'terbilang' => ucwords(trim($terbilang($record->total_harga ?? 0))) . " Rupiah",
        ]);
    })->name('print.transaksi');


    Route::get('/input-hasil/{record}/cetak', App\Http\Controllers\CetakHasilController::class)->name('cetak.hasil');

    Route::get('/cetak/hasil-bulk', App\Http\Controllers\BulkCetakHasilController::class)->name('cetak.hasil.bulk');
});
