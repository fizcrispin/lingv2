<?php

namespace App\Http\Controllers;

use App\Models\PendaftarLingkungan;
use Illuminate\Http\Request;

class BulkCetakHasilController extends Controller
{
    public function __invoke(Request $request)
    {
        $ids = explode(',', $request->query('ids', ''));
        $records = PendaftarLingkungan::whereIn('id', $ids)
                    ->with(['jenisSampel', 'hasilLingkungans.parameter.kategoriData'])
                    ->get();

        $printData = $records->map(function ($record) {
            // Group results by category
            $groupedResults = $record->hasilLingkungans->sortBy(function ($item) {
                return $item->parameter->kategoriData->nama_kategori ?? 'Z-Other';
            })->groupBy(function ($item) {
                return $item->parameter->kategoriData->nama_kategori ?? 'Tanpa Kategori';
            });

            // Determine "Tanggal Uji"
            $tglTerima = $record->created_at;
            $tglUji = $record->hasilLingkungans->max('tanggal_input') ?? now();

            return [
                'record' => $record,
                'groupedResults' => $groupedResults,
                'tglTerima' => $tglTerima,
                'tglUji' => $tglUji,
            ];
        });

        return view('cetak.hasil_lab_bulk', [
            'printData' => $printData,
        ]);
    }
}
