<!DOCTYPE html>
<html>
<head>
    <title>Print Label {{ $record->id }}</title>
    <style>
        @page { size: 50mm 20mm portrait; margin: 0; }
        body { 
            margin: 1mm; 
            padding: 0;
            font-family: Arial, sans-serif;
            font-weight: bold;
            text-align: left; 
            font-size: 10px; /* Base size assumption */
        }
        .label { 
            width: 50mm;
            height: 20mm;
            margin: 1mm;
            padding: 1mm;
            margin-top: -1mm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: flex-start;
            white-space: normal; 
            word-wrap: break-word; 
            overflow-wrap: break-word; 
            overflow: hidden;
            line-height: 1.2;
        }
        @media print {
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="label">
        <strong>NO. {{ $record->no_pendaftar }} | {{ $record->tanggal_pendaftar ? \Carbon\Carbon::parse($record->tanggal_pendaftar)->format('d/m/y') : '-' }} | 
        @php
            $inisial = '';
            $namaSampel = $record->jenisSampel->nama_sampel ?? '';
            if (!empty($namaSampel)) {
                $words = explode(' ', $namaSampel);
                foreach ($words as $w) {
                    $inisial .= strtoupper(substr($w, 0, 1));
                }
            }
            echo $inisial;
        @endphp
        </strong><br>
        <strong>Titik: {{ $record->titik_sampling }}</strong><br>
        <strong style="font-size: 9px;">{{ $record->nama_pengirim }}</strong>
    </div>
</body>
</html>
