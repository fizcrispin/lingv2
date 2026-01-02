<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Laporan Hasil Uji</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 12px; line-height: 1.4; color: #000; margin: 0; padding: 0; }
        .page-container { width: 100%; max-width: 210mm; margin: 0 auto; padding: 20px; box-sizing: border-box; }
        
        /* Header */
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; font-weight: bold; }
        .header h2 { margin: 5px 0 0; font-size: 14px; font-weight: normal; }
        .header p { margin: 2px 0; font-size: 11px; }

        /* Title */
        .doc-title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 20px; text-decoration: underline; text-transform: uppercase; }

        /* Info Grid */
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 4px; vertical-align: top; }
        .info-label { width: 140px; font-weight: bold; }
        .info-colon { width: 10px; }

        /* Results Table */
        .results-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .results-table th, .results-table td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
        .results-table th { background-color: #f3f3f3; font-weight: bold; text-align: center; font-size: 11px; text-transform: uppercase; }
        .results-table td.center { text-align: center; }
        
        .category-row td { background-color: #e5e7eb; font-weight: bold; text-align: left; text-transform: uppercase; padding: 8px; }

        /* Footer / Signature */
        .footer-section { display: flex; justify-content: space-between; margin-top: 50px; page-break-inside: avoid; }
        .signature-box { width: 200px; text-align: center; }
        .signature-box p { margin: 0; }
        .signature-space { height: 80px; }
        
        /* Page Break Logic */
        .print-page { page-break-after: always; min-height: 297mm; position: relative; }
        .print-page:last-child { page-break-after: auto; }

        @media print {
            body { margin: 0; -webkit-print-color-adjust: exact; }
            .page-container { padding: 0; max-width: none; margin: 0; }
            .print-page { margin: 0; padding: 1.5cm; box-sizing: border-box; height: auto; min-height: auto; }
            @page { margin: 0; size: A4 portrait; }
        }
    </style>
</head>
<body onload="window.print()">

    @foreach($printData as $data)
        @php
            $record = $data['record'];
            $groupedResults = $data['groupedResults'];
            $tglTerima = $data['tglTerima'];
            $tglUji = $data['tglUji'];
        @endphp

        <div class="print-page">
            <div class="header">
                <h1>Laboratorium Lingkungan</h1>
                <h2>PT. Lingkungan Sehat Sejahtera</h2>
                <p>Jl. Contoh No. 123, Kota Besar, Indonesia | Telp: (021) 12345678</p>
                <p>Email: lab@lingkungansehat.com | Website: www.lingkungansehat.com</p>
            </div>

            <h3 class="doc-title">Laporan Hasil Uji</h3>

            <table class="info-table">
                <tr>
                    <td class="info-label">No. Pendaftaran</td>
                    <td class="info-colon">:</td>
                    <td>{{ $record->no_pendaftar }}</td>
                    
                    <td class="info-label">Tgl. Terima</td>
                    <td class="info-colon">:</td>
                    <td>{{ $record->created_at->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="info-label">Nama Pengirim</td>
                    <td class="info-colon">:</td>
                    <td>{{ $record->nama_pengirim }}</td>

                    <td class="info-label">Tgl. Analisa</td>
                    <td class="info-colon">:</td>
                    <td>{{ \Carbon\Carbon::parse($tglUji)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="info-label">Jenis Sampel</td>
                    <td class="info-colon">:</td>
                    <td>{{ $record->jenisSampel->nama_sampel ?? '-' }}</td>

                    <td class="info-label">Alamat Sampling</td>
                    <td class="info-colon">:</td>
                    <td>{{ $record->alamat_sampling ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Keterangan</td>
                    <td class="info-colon">:</td>
                    <td colspan="4">{{ $record->keterangan ?? '-' }}</td>
                </tr>
            </table>

            <table class="results-table">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="30%">Parameter</th>
                        <th width="15%">Satuan</th>
                        <th width="10%">Batas Max</th>
                        <th width="15%">Hasil Uji</th>
                        <th width="25%">Metode</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @foreach($groupedResults as $kategori => $items)
                        <tr class="category-row">
                            <td colspan="6">{{ $kategori }}</td>
                        </tr>
                        @foreach($items as $item)
                            <tr>
                                <td class="center">{{ $no++ }}</td>
                                <td>{{ $item->nama_parameter }}</td>
                                <td class="center">{{ $item->parameter->satuan ?? '-' }}</td>
                                <td class="center">{{ $item->parameter->batas_max ?? '-' }}</td>
                                <td class="center" style="font-weight: bold;">{{ $item->hasil_parameter ?? '-' }}</td>
                                <td>{{ $item->parameter->metode_pemeriksaan ?? '-' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>

            <div class="footer-section">
                <div class="signature-box"></div> 
                
                <div class="signature-box">
                    <p>Kota Besar, {{ now()->translatedFormat('d F Y') }}</p>
                    <p>Manajer Teknis</p>
                    <div class="signature-space"></div>
                    <p style="text-decoration: underline; font-weight: bold;">( Nama Manajer )</p>
                </div>
            </div>

            <div style="margin-top: 20px; font-size: 10px; font-style: italic;">
                <p>Catatan:</p>
                <ol>
                    <li>Hasil uji ini hanya berlaku untuk sampel yang diuji.</li>
                    <li>Laporan ini tidak boleh digandakan kecuali secara lengkap dan dengan persetujuan tertulis dari laboratorium.</li>
                </ol>
            </div>
        </div>
    @endforeach

</body>
</html>
