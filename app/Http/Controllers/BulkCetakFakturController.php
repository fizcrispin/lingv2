<?php

namespace App\Http\Controllers;

use App\Models\InvoiceLingkungan;
use Illuminate\Http\Request;

class BulkCetakFakturController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // 1. Get IDs from request
        $ids = explode(',', $request->query('ids', ''));
        if (empty($ids)) {
             abort(404, 'No IDs provided');
        }

        // 2. Fetch Records with Relationships
        $records = InvoiceLingkungan::with('pendaftar.paket', 'pendaftar.jenisSampel')
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(function($query) {
                return (int) $query->pendaftar->no_pendaftar;
            })
            ->values();

        if ($records->isEmpty()) {
            abort(404, 'Records not found');
        }

        // 3. Validation
        // Ensure all records have the same Nama Pengirim and Jenis Sampel
        $first = $records->first();
        $refPengirim = $first->pendaftar->nama_pengirim;
        $refJenisSampel = $first->pendaftar->jenisSampel->id ?? null;

        foreach ($records as $r) {
            if ($r->pendaftar->nama_pengirim !== $refPengirim) {
                return "Error: Data yang dipilih memiliki Nama Pengirim yang berbeda ({$r->pendaftar->nama_pengirim} vs {$refPengirim}).";
            }
            if (($r->pendaftar->jenisSampel->id ?? null) !== $refJenisSampel) {
                return "Error: Data yang dipilih memiliki Jenis Sampel yang berbeda.";
            }
        }

        // 4. Prepare Aggregated Data
        
        // Calculate Invoice Number Range
        // Assuming no_pendaftar is numeric or sortable string.
        $numbers = $records->map(fn($r) => $r->pendaftar->no_pendaftar ?? 0)->filter()->sort();
        $min = $numbers->first();
        $max = $numbers->last();
        $range = ($min === $max) ? $min : "$min-$max";

        // Calculate Total
        $totalHarga = $records->sum('total_harga') ?? 0;

        // Terbilang Logic
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

        $terbilangText = ucwords(trim($terbilang($totalHarga))) . " Rupiah";

        return view('cetak.faktur_bulk', [
            'records' => $records,
            'title' => 'Faktur Gabungan',
            'range' => $range,
            'totalHarga' => $totalHarga,
            'terbilang' => $terbilangText,
            'first' => $first, // Reference record for header info
        ]);
    }
}
