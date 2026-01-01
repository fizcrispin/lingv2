<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - {{ $record->pendaftar->no_pendaftar ?? 'Transaksi' }}</title>
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; color: #333; line-height: 1.5; margin: 0; padding: 20px; background: #f4f4f4; }
        .no-print-wrapper { text-align: center; margin-bottom: 20px; }
        .container { max-width: 800px; margin: auto; border: 1px solid #ddd; padding: 40px; background: white; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        .header { display: flex; justify-content: space-between; border-bottom: 3px solid #0d9488; padding-bottom: 15px; margin-bottom: 25px; }
        .logo-area h1 { margin: 0; font-size: 26px; color: #0d9488; letter-spacing: -0.5px; }
        .logo-area p { margin: 2px 0; font-size: 11px; color: #666; text-transform: uppercase; }
        .doc-title { text-align: right; }
        .doc-title h2 { margin: 0; font-size: 22px; text-transform: uppercase; color: #111; }
        .doc-title p { margin: 2px 0; font-size: 13px; color: #444; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 35px; }
        .info-block h4 { margin: 0 0 12px 0; border-bottom: 1px solid #eee; padding-bottom: 6px; font-size: 13px; text-transform: uppercase; color: #0d9488; }
        .info-block p { margin: 4px 0; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f8fafc; text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; font-size: 12px; text-transform: uppercase; color: #64748b; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }
        .text-right { text-align: right; }
        .total-area { text-align: right; margin-top: 20px; border-top: 2px solid #0d9488; padding-top: 15px; }
        .total-row { display: flex; justify-content: flex-end; gap: 40px; margin-bottom: 5px; font-size: 14px; }
        .total-label { color: #64748b; }
        .total-value { font-weight: bold; width: 150px; }
        .grand-total { font-size: 18px; color: #0d9488; margin-top: 10px; }
        .footer { margin-top: 60px; display: grid; grid-template-columns: 1fr 1fr; text-align: center; }
        .signature { height: 90px; }
        @media print {
            body { padding: 0; background: white; }
            .container { border: none; box-shadow: none; max-width: 100%; }
            .no-print-wrapper { display: none; }
        }
        .btn-print { background: #0d9488; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 14px; transition: background 0.2s; }
        .btn-print:hover { background: #0f766e; }
    </style>
</head>
<body>
    <div class="no-print-wrapper">
        <button class="btn-print" onclick="window.print()">Cetak Dokumen Sekarang</button>
    </div>

    @if($record->pendaftar)
    <div class="container">
        <div class="header">
            <div class="logo-area">
                <h1>LABORATORIUM LINGKUNGAN</h1>
                <p>Unit Pelaksana Teknis Laboratorium Lingkungan</p>
                <p>Jl. Contoh Alamat No. 123, Kota, Indonesia</p>
                <p>Telp: (021) 12345678 | Email: lab@contoh.com</p>
            </div>
            <div class="doc-title">
                <h2>{{ $title }}</h2>
                <p><strong>#{{ $record->pendaftar->no_pendaftar }}</strong></p>
                <p>{{ $record->tanggal_tagihan?->format('d/m/Y') ?? now()->format('d/m/Y') }}</p>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-block">
                <h4>Pelanggan / Pengirim:</h4>
                <p><strong>{{ $record->pendaftar->nama_pengirim }}</strong></p>
                <p>{{ $record->pendaftar->titik_sampling }}</p>
                <p>Sampel: {{ $record->pendaftar->jenisSampel->nama_sampel ?? '-' }}</p>
            </div>
            <div class="info-block">
                <h4>Informasi Pembayaran:</h4>
                <p>Status: <strong style="color: {{ $record->status_bayar === 2 ? '#059669' : '#dc2626' }}">
                    @if($record->status_bayar === 0) DRAFT 
                    @elseif($record->status_bayar === 1) MENUNGGU PEMBAYARAN 
                    @elseif($record->status_bayar === 2) LUNAS 
                    @else STATUS: {{ $record->status_bayar }} @endif
                </strong></p>
                @if($record->tanggal_bayar)
                    <p>Tgl Pelunasan: {{ $record->tanggal_bayar->format('d/m/Y') }}</p>
                @endif
                @if($record->metode_pembayaran)
                    <p>Metode: {{ $record->metode_pembayaran }}</p>
                @endif
                @if($record->kode_ver)
                    <p>Ref: {{ $record->kode_ver }}</p>
                @endif
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">No</th>
                    <th>Parameter Pengujian</th>
                    <th class="text-right">Biaya (IDR)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($parameters as $index => $param)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $param->nama_parameter }}</td>
                    <td class="text-right">{{ number_format($param->harga_parameter, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                @if($parameters->isEmpty())
                <tr>
                    <td colspan="3" style="text-align: center; color: #94a3b8; font-style: italic;">Tidak ada rincian parameter</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="total-area">
            <div class="total-row">
                <span class="total-label">Subtotal</span>
                <span class="total-value">{{ number_format($record->total_harga, 0, ',', '.') }}</span>
            </div>
            <div class="total-row">
                <span class="total-label">Sudah Dibayar</span>
                <span class="total-value">{{ number_format($record->total_bayar, 0, ',', '.') }}</span>
            </div>
            <div class="total-row grand-total">
                <span class="total-label" style="color: #0d9488;">{{ $record->status_bayar === 2 ? 'TOTAL LUNAS' : 'SISA TAGIHAN' }}</span>
                <span class="total-value">IDR {{ number_format($record->total_harga - $record->total_bayar, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="footer">
            <div>
                <p>Customer,</p>
                <div class="signature"></div>
                <p>( ____________________ )</p>
            </div>
            <div>
                <p>Petugas Administrasi,</p>
                <div class="signature"></div>
                <p>( {{ auth()->user()->name ?? 'Petugas Lab' }} )</p>
            </div>
        </div>
    </div>
    @else
    <div class="container" style="text-align: center; color: #dc2626; padding: 100px;">
        <h3>Error: Data Pendaftar Tidak Ditemukan</h3>
        <p>Silakan periksa kembali kuitansi ini atau hubungi administrator.</p>
    </div>
    @endif
</body>
</html>
