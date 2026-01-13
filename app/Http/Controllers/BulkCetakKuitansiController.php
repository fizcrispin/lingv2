<?php

namespace App\Http\Controllers;

use App\Models\InvoiceLingkungan;
use Illuminate\Http\Request;

class BulkCetakKuitansiController extends Controller
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
            ->get();

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

        // 5. Aggregate Parameters
        $paramCounts = [];
        foreach ($records as $r) {
            $p = $r->pendaftar;
            $ids = [];
            
            // Manual
            if (is_array($p->parameter)) $ids = array_merge($ids, $p->parameter);
            
            // Paket
            if ($p->paket_id && $p->paket && is_array($p->paket->parameter)) {
                $ids = array_merge($ids, $p->paket->parameter);
            }
            
            // Unique per record? Or count all occurrences?
            // "jika parameter sama maka dibuat kurung berapa jumlahnya" implies count per usage per record.
            // But usually a single record (invoice) has unique parameters.
            // So we count how many invoices contain 'Salmonella'.
            
            $uniqueParamsInRecord = array_unique($ids);
            foreach ($uniqueParamsInRecord as $pid) {
                if (!isset($paramCounts[$pid])) $paramCounts[$pid] = 0;
                $paramCounts[$pid]++;
            }
        }
        
        $parameterString = "-";
        if (!empty($paramCounts)) {
            // Get Names
            $paramModels = \App\Models\ParameterLingkungan::whereIn('id', array_keys($paramCounts))->pluck('nama_parameter', 'id');
            
            $parts = [];
            foreach ($paramCounts as $pid => $count) {
                $name = $paramModels[$pid] ?? 'Unknown';
                if ($count > 1) {
                    $parts[] = "{$name} ({$count})";
                } else {
                    $parts[] = $name;
                }
            }
            $parameterString = implode(', ', $parts);
        }

        return view('cetak.kuitansi_bulk', [
            'records' => $records,
            'title' => 'Kuitansi Gabungan',
            'range' => $range,
            'totalHarga' => $totalHarga,
            'terbilang' => $terbilangText,
            'first' => $first, // Reference record for header info
            'parameterString' => $parameterString,
        ]);
    }
}
