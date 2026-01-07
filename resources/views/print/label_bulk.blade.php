<!DOCTYPE html>
<html>
<head>
    <title>Bulk Print Label</title>
    <style>
        @page { size: 50mm 20mm portrait; margin: 0; }
        body { 
            margin: 0; 
            padding: 0;
            font-family: Arial, sans-serif;
            font-weight: bold;
            text-align: left; 
            font-size: 10px; 
            -webkit-print-color-adjust: exact;
        }
        .label-container {
            width: 50mm;
            height: 20mm;
            page-break-after: always; /* Force new page for each label */
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: flex-start;
            padding: 1mm;
            margin-top: -1mm;
            box-sizing: border-box;
            white-space: normal; 
            word-wrap: break-word; 
            overflow-wrap: break-word; 
            overflow: hidden;
            line-height: 1.2;
        }
        /* Remove page break for the last element to avoid empty page */
        .label-container:last-child {
            page-break-after: auto;
        }
    </style>
</head>
<body onload="window.print()">
    @foreach ($records as $record)
        <div class="label-container">
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
    @endforeach
</body>
</html>
