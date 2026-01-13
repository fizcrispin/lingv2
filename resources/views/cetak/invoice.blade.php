@php
    // --- KONFIGURASI KERTAS & MARGIN (Faktur / Kuitansi) ---
    $paperSize = '215mm 330mm'; // Ukuran Kertas (F4 Portrait). Contoh: '215mm 330mm' (F4), 'Letter', 'auto'
    $pageMargin = '1rem';         // Margin Halaman (Cetak)
    $bodyPadding = '1rem';       // Padding Konten dalam kertas (jarak isi dari tepi)
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $record->pendaftar->no_pendaftar ?? 'Transaksi' }}-{{ $record->pendaftar->nama_pengirim ?? '-' }}-{{ $record->pendaftar->titik_sampling ?? '-' }}-{{ $title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Times+New+Roman&display=swap');
        body { 
            font-family: Arial, Helvetica, sans-serif; 
            color: black; 
            background: white; 
            -webkit-print-color-adjust: exact; 
            font-size: 12pt;
        }

        .no-print-wrapper { 
            text-align: center; 
            margin-bottom: 10px; 
        }

        @media print {
            .no-print-wrapper { 
                display: none; 
            }

            body { 
                padding: 1mm !important; 
                margin: 1mm !important; 
                background: white;
            }

            @page { 
                margin: {{ $pageMargin }}; 
                size: {{ $paperSize }}; 
            }
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadPDF() {
            const element = document.getElementById('print');
            const opt = {
                margin: 10,
                filename: '{{ $title }}-{{ $record->pendaftar->no_pendaftar }}.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true, letterRendering: true },
                jsPDF: { unit: 'mm', format: [215, 330], orientation: 'portrait',compress: true }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
</head>
<body class="bg-gray-100" style="padding: {{ $bodyPadding }}">
    <div class="no-print-wrapper mt-3 flex justify-center gap-4 mb-8 print:hidden">
        <button class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 font-sans" onclick="window.print()">Cetak Dokumen</button>
        <button class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700 font-sans" onclick="downloadPDF()">Simpan PDF</button>
        <button class="bg-gray-500 text-white px-4 py-2 rounded shadow hover:bg-gray-600 font-sans" onclick="window.close()">Tutup</button>
    </div>

    <div id="print" class="max-w-[215mm] mx-auto bg-white">
        <!-- HEADER (Common) -->
        <div class="relative border-b-[3px] border-black pb-1 mb-0">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/d/d3/Seal_of_Sragen_Regency.svg/250px-Seal_of_Sragen_Regency.svg.png" alt="Logo" class="absolute left-1 top-0 h-[80px] object-contain">
            <div class="w-full text-center">
                <h5 class="uppercase font-bold text-sm mb-[2px] leading-tight">Pemerintah Kabupaten Sragen</h5>
                <h5 class="uppercase font-bold text-sm mb-[2px] leading-tight">Dinas Kesehatan</h5>
                <h4 class="font-bold text-base mb-[2px] leading-tight">UPTD LABORATORIUM KESEHATAN</h4>
                <small class="block text-xs leading-tight">Jalan Jend. Ahmad Yani, Sragen, Jawa Tengah 57216</small>
                <small class="block text-xs leading-tight">Telepon (0271) 891078, Laman labkesda.sragenkab.go.id, Pos-el labkesda.sragen@gmail.com</small>
            </div>
        </div>

        @if(isset($type) && $type === 'kuitansi')
        <!-- KUITANSI LAYOUT -->
        <div class="text-center py-1 mb-1">
            <h5 class="underline uppercase font-bold tracking-[5px] text-lg">KUITANSI</h5>
        </div>

        <div class="px-5 text-xs">
             <!-- Nomor -->
            <div class="flex mb-1 text-xs">
                <div class="w-3/12 flex justify-between pr-4"><span>Nomor</span><span>:</span></div>
                <div class="w-9/12">400.7.5./{{ $record->pendaftar->no_pendaftar ?? '-' }}/K/LL/05.3.1/{{ $record->pendaftar->tanggal_pendaftar ? $record->pendaftar->tanggal_pendaftar->format('Y') : now()->format('Y') }}</div>
            </div>

            <!-- Tanggal Pendaftaran -->
            <div class="flex mb-1 text-xs">
                <div class="w-3/12 flex justify-between pr-4"><span>Tanggal Pendaftaran</span><span>:</span></div>
                <div class="w-9/12">
                    {{ $record->pendaftar->tanggal_pendaftar ? $record->pendaftar->tanggal_pendaftar->locale('id')->translatedFormat('d F Y') : '-' }}
                </div>
            </div>

            <!-- Telah Diterima Dari -->
            <div class="flex mb-1 text-xs">
                <div class="w-3/12 flex justify-between pr-1"><span>Telah diterima dari</span><span>:</span></div>
                <div class="w-9/12 uppercase font-bold">
                    {{ $record->pendaftar->nama_pengirim ?? '-' }}
                </div>
            </div>
            <div class="flex mb-1 text-xs">
                <div class="w-3/12 flex justify-between pr-1"></div>
                <div class="w-9/12">
                    {{ $record->pendaftar->alamat_pengirim ?? '-' }}
                </div>
            </div>

             <!-- Pembayaran Untuk -->
             <div class="flex mb-1 text-xs">
                <div class="w-3/12 flex justify-between pr-1"><span>Untuk Pembayaran</span><span>:</span></div>
                <div class="w-9/12">
                    Pemeriksaan Laboratorium Lingkungan
                    <br>
                    <span class="text-[12px]">
                        {{ $record->pendaftar->jenisSampel->nama_sampel ?? '-' }} : {{ $record->pendaftar->titik_sampling ?? '-' }}
                    </span>
                    <br>
                    <span class="italic text-[11px]">
                        ({{ $parameters->pluck('nama_parameter')->join(', ') }})
                    </span>
                </div>
            </div>

            <!-- Sejumlah Uang -->
            <div class="flex mb-1 text-xs">
                <div class="w-3/12 flex justify-between pr-1"><span>Sejumlah Uang</span><span>:</span></div>
                <div class="w-9/12 font-bold">
                   Rp {{ number_format($record->total_harga ?? 0, 0, ',', '.') }}
                </div>
            </div>

            <!-- Terbilang -->
             <div class="flex mb-1 text-xs">
                <div class="w-3/12 flex justify-between pr-1"><span>Terbilang</span><span>:</span></div>
                <div class="w-9/12 font-bold italic capitalize">
                   <span class="bg-gray-100 p-1 rounded inline-block"># {{ $terbilang ?? '-' }} #</span>
                </div>
            </div>
        </div>

        <!-- Footer Kuitansi -->
         <div class="mt-2 pt-2">
            <div class="grid grid-cols-2 gap-4">
                <div></div>
                <div class="text-center text-xs">
                    <p class="mb-0">Sragen, {{ $record->tanggal_bayar ? $record->tanggal_bayar->locale('id')->translatedFormat('d F Y') : 'Belum Bayar' }}</p>
                </div>
            </div>

            <div class="text-center text-xs grid grid-cols-2 gap-8">
                <!-- Left Sig -->
                <div>
                     <p>Mengetahui</p>
                     <p class="mb-12">Bendahara Penerimaan BLUD</p>
                     <p class="font-bold underline">
                        HENI ISDARYANTI,AM.AK
                     </p>
                     <p>NIP. 19780130 202121 2 004</p>
                </div>

                <!-- Right Sig -->
                <div>
                    <p>&nbsp;</p>
                    <p class="mb-12">Penerima</p>
                    <p class="font-bold border-b border-black inline-block min-w-[150px]">&nbsp;</p>
               </div>
            </div>
         </div>

        @else
        <!-- FAKTUR PENAGIHAN LAYOUT -->
        <div class="text-center py-1 mb-1">
            <h5 class="underline uppercase font-bold tracking-[5px] text-lg">FAKTUR</h5>
        </div>

        <!-- INFO SECTION -->
        <div class="flex flex-wrap -mx-2 mb-1 text-xs">
            <!-- Left Column: Sender Info -->
            <div class="w-7/12 px-1">
                <div class="w-10/12">
                    <span class="block text-xs mb-1">Kepada Yth:</span>
                    <div class="flex justify-between mb-1">
                        <span class="font-bold uppercase">{{ $record->pendaftar->nama_pengirim ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="uppercase">{{ $record->pendaftar->alamat_pengirim ?? $record->pendaftar->alamat_sampling ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Transaksi Info -->
            <div class="w-5/12 px-1 space-y-1">
                <div class="flex text-xs">
                    <div class="w-3/12">Nomor</div>
                    <div class="w-1/12 text-center">:</div>
                    <div class="w-8/12 ">400.7.5./{{ $record->pendaftar->no_pendaftar ?? '-' }}/F/LL/05.3.1/{{ $record->pendaftar->tanggal_pendaftar ? $record->pendaftar->tanggal_pendaftar->format('Y') : now()->format('Y') }}</div>
                </div>
                <div class="flex text-xs">
                    <div class="w-3/12">Jenis Sampel</div>
                    <div class="w-1/12 text-center">:</div>
                    <div class="w-8/12">{{ $record->pendaftar->jenisSampel->nama_sampel ?? '-' }}</div>
                </div>
                <div class="flex text-xs">
                    <div class="w-3/12">Titik Sampling</div>
                    <div class="w-1/12 text-center">:</div>
                    <div class="w-8/12">{{ $record->pendaftar->titik_sampling ?? '-' }}</div>
                </div>
                <div class="flex text-xs">
                    <div class="w-3/12">Tgl Daftar</div>
                    <div class="w-1/12 text-center">:</div>
                    <div class="w-8/12">{{ $record->pendaftar->tanggal_pendaftar ? \Carbon\Carbon::parse($record->pendaftar->tanggal_pendaftar)->format('d-m-Y') : '-' }}</div>
                </div>
            </div>
        </div>

        <!-- TABLE SECTION -->
        <div class="w-full mb-1">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="border-t border-b-[1px] border-gray-500">
                        <th class="py-2 text-center w-[5%] uppercase font-bold">No</th>
                        <th class="py-2 text-center uppercase font-bold">Parameter</th>
                        <th class="py-2 text-center w-[10%] uppercase font-bold">QTY</th>
                        <th class="py-2 text-center w-[20%] uppercase font-bold">Harga</th>
                        <th class="py-2 text-center w-[20%] uppercase font-bold">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parameters as $index => $param)
                    <tr class="border-b border-b-[0.1px] border-gray-500">
                        <td class="py-0 text-center">{{ $index + 1 }}</td>
                        <td class="py-0 ">{{ $param->nama_parameter }}</td>
                        <td class="py-0 text-center">1</td>
                        <td class="py-0 text-right">{{ number_format($param->harga_parameter, 0, ',', '.') }}</td>
                        <td class="py-0 text-right">{{ number_format($param->harga_parameter, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    
                    <!-- TOTAL ROW -->
                    <tr class="border-t-[1px] border-gray-500">
                        <td colspan="3"></td>
                        <td class="py-2 text-right uppercase font-bold">Total</td>
                        <td class="py-2 text-right font-bold text-black border-l-0">
                            Rp {{ number_format($record->total_harga, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- FOOTER SECTION -->
        <div class="flex flex-wrap mt-1">
            <!-- Payment Info -->
            <div class="w-7/12 pr-1">
                <strong class="block text-xs mb-1">Informasi Pembayaran</strong>
                <p class="text-[11px] leading-snug mb-1">
                    Bank Jateng No. Rek. <b>1010-0116-60 a.n. UPTD Labkesda Sragen</b><br>
                    Format Transfer ditulis pada kolom <b>berita/keterangan</b>:
                </p>
                <div class="inline-block p-1 border-[2px] border-dashed border-black bg-white rounded text-xs font-bold px-2 py-1">
                    {{ $record->pendaftar->tanggal_pendaftar ? $record->pendaftar->tanggal_pendaftar->format('ymd') : now()->format('ymd') }}{{ $record->pendaftar->no_pendaftar }}
                </div>
                <p class="italic text-[10px] mt-1 leading-tight">
                    * Pembayaran harap dilakukan paling lambat 14 hari setelah dokumen ini terbit.<br>
                    * Konfirmasi pembayaran melalui Whatsapp: <b>0851-4374-5050</b>
                </p>
            </div>

            <!-- Signature -->
            <div class="w-5/12 flex flex-col justify-end items-center text-center">
                <div class="mb-12 text-xs">
                    <span>Sragen, {{ $record->tanggal_tagihan ? $record->tanggal_tagihan->locale('id')->translatedFormat('d F Y') : now()->locale('id')->translatedFormat('d F Y') }}</span><br>
                    <span>Penerima</span>
                </div>
                <div class="w-40 border-b border-black pb-1 text-xs">
                    <!-- {{ auth()->user()->name ?? 'Petugas' }} -->
                </div>
            </div>
        </div>
        @endif
    </div>
</body>
</html>
