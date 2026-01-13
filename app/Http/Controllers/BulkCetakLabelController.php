<?php

namespace App\Http\Controllers;

use App\Models\PendaftarLingkungan;
use Illuminate\Http\Request;

class BulkCetakLabelController extends Controller
{
    public function __invoke(Request $request)
    {
        $ids = explode(',', $request->query('ids', ''));
        $records = PendaftarLingkungan::whereIn('id', $ids)
                    ->with('jenisSampel')
                    ->orderByRaw('CAST(no_pendaftar AS UNSIGNED) DESC') // Optional: Order by no_pendaftar
                    ->get();

        return view('cetak.label_bulk', [
            'records' => $records,
        ]);
    }
}
