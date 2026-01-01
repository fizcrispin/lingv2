<?php

namespace App\Http\Controllers;

use App\Models\PendaftarLingkungan;
use Illuminate\Http\Request;

class CetakHasilController extends Controller
{
    public function __invoke(PendaftarLingkungan $record)
    {
        // Eager load data needed for printing
        $record->load(['jenisSampel', 'hasilLingkungans.parameter.kategoriData']);

        // Group results by category
        $groupedResults = $record->hasilLingkungans->sortBy(function ($item) {
            return $item->parameter->kategoriData->nama_kategori ?? 'Z-Other';
        })->groupBy(function ($item) {
            return $item->parameter->kategoriData->nama_kategori ?? 'Tanpa Kategori';
        });

        // Determine "Tanggal Uji" (Using created_at of Pendaftar as received date, and input date as test date range)
        $tglTerima = $record->created_at;
        $tglUji = $record->hasilLingkungans->max('tanggal_input') ?? now();

        return view('cetak.hasil_lab', [
            'record' => $record,
            'groupedResults' => $groupedResults,
            'tglTerima' => $tglTerima,
            'tglUji' => $tglUji,
        ]);
    }
}
