<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
    <table>
        <thead>
        <tr>
            <th colspan="3" style="font-weight: bold; font-size: 16px; text-align: center;">LAPORAN OPERASIONAL &amp; KEUANGAN</th>
        </tr>
        <tr>
            <th colspan="3" style="text-align: center;">Periode: {{ $start_date }} s/d {{ $end_date }}</th>
        </tr>
        <tr>
            <th colspan="3"></th>
        </tr>
        </thead>
        <tbody>
            <!-- PENDAFTARAN -->
            <tr>
                <td colspan="3" style="font-weight: bold; background-color: #eeeeee;">PENDAFTARAN</td>
            </tr>
            <tr>
                <td>Total Pendaftar</td>
                <td colspan="2" style="font-weight: bold;">{{ number_format($pendaftaran_count, 0, ',', '.') }}</td>
            </tr>
            
            <tr><td colspan="3"></td></tr>
            
            <tr>
                <td colspan="3" style="font-weight: bold;">Distribusi Jenis Sampel</td>
            </tr>
            @foreach($jenis_sampel_stats as $jenis => $count)
            <tr>
                <td>{{ $jenis }}</td>
                <td colspan="2">{{ $count }}</td>
            </tr>
            @endforeach

            <tr><td colspan="3"></td></tr>

            <tr>
                <td colspan="3" style="font-weight: bold;">Top Parameter</td>
            </tr>
            @foreach($parameter_stats as $param => $count)
            <tr>
                <td>{{ $param }}</td>
                <td colspan="2">{{ $count }}</td>
            </tr>
            @endforeach

            <tr><td colspan="3"></td></tr>

            <!-- EKSPEDISI -->
            <tr>
                <td colspan="3" style="font-weight: bold; background-color: #eeeeee;">EKSPEDISI &amp; LABORATORIUM</td>
            </tr>
            <tr>
                <td>Rata-rata Waktu Tunggu</td>
                <td colspan="2" style="font-weight: bold;">{{ $avg_verifikasi_time }}</td>
            </tr>
            <tr>
                <td colspan="3" style="font-weight: bold;">Status Sampel</td>
            </tr>
            @foreach($ekspedisi_stats as $stat => $count)
            <tr>
                <td>{{ $stat }}</td>
                <td colspan="2">{{ $count }}</td>
            </tr>
            @endforeach

            <tr><td colspan="3"></td></tr>

            <!-- KEUANGAN -->
            <tr>
                <td colspan="3" style="font-weight: bold; background-color: #eeeeee;">KEUANGAN</td>
            </tr>
            <tr>
                <td>Total Pendapatan (Lunas)</td>
                <td colspan="2" style="font-weight: bold;">Rp {{ number_format($total_revenue, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Invoice Belum Lunas</td>
                <td colspan="2">{{ number_format($total_unpaid, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Invoice Lunas</td>
                <td colspan="2">{{ number_format($total_paid, 0, ',', '.') }}</td>
            </tr>
            
            <tr><td colspan="3"></td></tr>
            
            <tr>
                <td colspan="3" style="font-weight: bold;">Metode Pembayaran</td>
            </tr>
            @foreach($payment_method_stats as $method => $count)
            <tr>
                <td>{{ $method ?: 'Lainnya' }}</td>
                <td colspan="2">{{ $count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
