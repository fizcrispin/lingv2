<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - {{ $record->pendaftar->no_pendaftar ?? 'Transaksi' }}</title>
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
            margin-bottom: 20px; 
        }

        @media print {
            .no-print-wrapper { 
                display: none; 
            }

            body { 
                padding: 0; 
                margin: 0; 
            }

            @page { 
                margin: 1cm; 
                size: auto; 
            }
        }
    </style>
</head>
<body class="p-8 bg-gray-100">
    <div class="no-print-wrapper mt-3 flex justify-center gap-4 mb-8 print:hidden">
        <button class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 font-sans" onclick="window.print()">Cetak Dokumen</button>
        <button class="bg-gray-500 text-white px-4 py-2 rounded shadow hover:bg-gray-600 font-sans" onclick="window.close()">Tutup</button>
    </div>

    <div id="print" class="max-w-[210mm] mx-auto bg-white">
        <!-- HEADER (Common) -->
        <div class="relative border-b-[3px] border-black pb-4 mb-5">
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
        <div class="text-center py-2 mb-6">
            <h5 class="underline uppercase font-bold tracking-[5px] text-lg">KUITANSI</h5>
        </div>

        <div class="px-5 text-xs">
             <!-- Nomor -->
            <div class="flex mb-2 text-xs">
                <div class="w-3/12 flex justify-between pr-4"><span>Nomor</span><span>:</span></div>
                <div class="w-9/12">400.7.5./{{ $record->pendaftar->no_pendaftar ?? '-' }}/K/LL/05.3.1/{{ $record->pendaftar->tanggal_pendaftar ? $record->pendaftar->tanggal_pendaftar->format('Y') : now()->format('Y') }}</div>
            </div>

            <!-- Tanggal Pendaftaran -->
            <div class="flex mb-2 text-xs">
                <div class="w-3/12 flex justify-between pr-4"><span>Tanggal Pendaftaran</span><span>:</span></div>
                <div class="w-9/12">
                    {{ $record->pendaftar->tanggal_pendaftar ? $record->pendaftar->tanggal_pendaftar->format('d/m/Y') : '-' }}
                </div>
            </div>

            <!-- Telah Diterima Dari -->
            <div class="flex mb-2 text-xs">
                <div class="w-3/12 flex justify-between pr-4"><span>Telah diterima dari</span><span>:</span></div>
                <div class="w-9/12 uppercase font-bold">
                    {{ $record->pendaftar->nama_pengirim ?? '-' }}
                </div>
            </div>

             <!-- Pembayaran Untuk -->
             <div class="flex mb-2 text-xs">
                <div class="w-3/12 flex justify-between pr-4"><span>Untuk Pembayaran</span><span>:</span></div>
                <div class="w-9/12">
                    Pemeriksaan Sampel {{ $record->pendaftar->jenisSampel->nama_sampel ?? '-' }}
                </div>
            </div>

            <!-- Sejumlah Uang -->
            <div class="flex mb-2 text-xs">
                <div class="w-3/12 flex justify-between pr-4"><span>Sejumlah Uang</span><span>:</span></div>
                <div class="w-9/12 font-bold">
                   Rp {{ number_format($record->total_harga ?? 0, 0, ',', '.') }}
                </div>
            </div>

            <!-- Terbilang -->
             <div class="flex mb-2 text-xs">
                <div class="w-3/12 flex justify-between pr-4"><span>Terbilang</span><span>:</span></div>
                <div class="w-9/12 font-bold italic capitalize">
                   <span class="bg-gray-100 p-1 rounded inline-block"># {{ $terbilang ?? '-' }} #</span>
                </div>
            </div>
        </div>

        <!-- Footer Kuitansi -->
         <div class="mt-8 pt-4">
            <div class="grid grid-cols-2 gap-4">
                <div></div>
                <div class="text-center text-xs">
                    <p class="mb-4">Sragen, {{ $record->tanggal_bayar ? $record->tanggal_bayar->locale('id')->translatedFormat('d F Y') : 'Belum Bayar' }}</p>
                </div>
            </div>

            <div class="text-center text-xs grid grid-cols-2 gap-8">
                <!-- Left Sig -->
                <div>
                     <p>Mengetahui</p>
                     <p class="mb-16">Bendahara Penerimaan BLUD</p>
                     <p class="font-bold underline">MEITA KINANTHI, A.Md.</p>
                     <p>NIP. 19950523 202421 2 028</p>
                </div>

                <!-- Right Sig -->
                <div>
                    <p>&nbsp;</p>
                    <p class="mb-16">Penerima</p>
                    <p class="font-bold border-b border-black inline-block min-w-[150px]">&nbsp;</p>
               </div>
            </div>
         </div>

        @else
        <!-- FAKTUR PENAGIHAN LAYOUT -->
        <div class="text-center py-2 mb-6">
            <h5 class="underline uppercase font-bold tracking-[5px] text-lg">FAKTUR</h5>
        </div>

        <!-- INFO SECTION -->
        <div class="flex flex-wrap -mx-2 mb-6 text-xs">
            <!-- Left Column: Sender Info -->
            <div class="w-7/12 px-2">
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
            <div class="w-5/12 px-2 space-y-1">
                <div class="flex text-xs">
                    <div class="w-5/12">Nomor</div>
                    <div class="w-1/12 text-center">:</div>
                    <div class="w-6/12 ">400.7.5./{{ $record->pendaftar->no_pendaftar ?? '-' }}/F/LL/05.3.1/{{ $record->pendaftar->tanggal_pendaftar ? $record->pendaftar->tanggal_pendaftar->format('Y') : now()->format('Y') }}</div>
                </div>
                <div class="flex text-xs">
                    <div class="w-5/12">Jenis Sampel</div>
                    <div class="w-1/12 text-center">:</div>
                    <div class="w-6/12">{{ $record->pendaftar->jenisSampel->nama_sampel ?? '-' }}</div>
                </div>
                <div class="flex text-xs">
                    <div class="w-5/12">Titik Sampling</div>
                    <div class="w-1/12 text-center">:</div>
                    <div class="w-6/12">{{ $record->pendaftar->titik_sampling ?? '-' }}</div>
                </div>
                <div class="flex text-xs">
                    <div class="w-5/12">Tgl Pendaftaran</div>
                    <div class="w-1/12 text-center">:</div>
                    <div class="w-6/12">{{ $record->pendaftar->tanggal_pendaftar ? \Carbon\Carbon::parse($record->pendaftar->tanggal_pendaftar)->format('d-m-Y') : '-' }}</div>
                </div>
            </div>
        </div>

        <!-- TABLE SECTION -->
        <div class="w-full mb-6">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="border-t border-b-[2px] border-black">
                        <th class="py-2 text-center w-[5%] uppercase font-bold">No</th>
                        <th class="py-2 text-center uppercase font-bold">Parameter</th>
                        <th class="py-2 text-center w-[10%] uppercase font-bold">QTY</th>
                        <th class="py-2 text-center w-[20%] uppercase font-bold">Harga</th>
                        <th class="py-2 text-center w-[20%] uppercase font-bold">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parameters as $index => $param)
                    <tr class="border-b border-b-[1px] border-black">
                        <td class="py-1 text-center">{{ $index + 1 }}</td>
                        <td class="py-1 ">{{ $param->nama_parameter }}</td>
                        <td class="py-1 text-center">1</td>
                        <td class="py-1 text-right">{{ number_format($param->harga_parameter, 0, ',', '.') }}</td>
                        <td class="py-1 text-right">{{ number_format($param->harga_parameter, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    
                    <!-- TOTAL ROW -->
                    <tr class="border-t-[2px] border-black">
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
        <div class="flex flex-wrap mt-8">
            <!-- Payment Info -->
            <div class="w-7/12 pr-4">
                <strong class="block text-xs mb-1">Informasi Pembayaran</strong>
                <p class="text-[11px] leading-snug mb-2">
                    BPD Bank Jateng No. Rek. <b>1-010-011660</b><br>
                    a.n. UPTD Labkesda Sragen<br><br>
                    Format Transfer ditulis pada kolom <b>berita/keterangan</b>:
                </p>
                <div class="inline-block p-2 border-2 border-dashed border-black bg-white rounded text-xs font-bold">
                    {{ $record->pendaftar->tanggal_pendaftar ? $record->pendaftar->tanggal_pendaftar->format('ymd') : now()->format('ymd') }}{{ $record->pendaftar->no_pendaftar }}
                </div>
                <p class="italic text-[10px] mt-3 leading-tight">
                    * Pembayaran harap dilakukan paling lambat 14 hari setelah dokumen ini terbit.<br>
                    * Konfirmasi pembayaran melalui Whatsapp: <b>0851-4374-5050</b>
                </p>
            </div>

            <!-- Signature -->
            <div class="w-5/12 flex flex-col justify-end items-center text-center">
                <div class="mb-12 text-xs">
                    <span>Sragen, {{ $record->pendaftar->tanggal_pendaftar ? $record->pendaftar->tanggal_pendaftar->locale('id')->translatedFormat('d F Y') : now()->locale('id')->translatedFormat('d F Y') }}</span><br>
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
