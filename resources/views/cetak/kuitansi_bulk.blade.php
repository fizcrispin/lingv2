@php
    // --- KONFIGURASI KERTAS & MARGIN (Faktur / Kuitansi) ---
    $paperSize = '215mm 330mm'; // Ukuran Kertas (F4 Portrait)
    $pageMargin = '1rem';         // Margin Halaman (Cetak)
    $bodyPadding = '1rem';       // Padding Konten dalam kertas
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Gabungan - {{ $first->pendaftar->nama_pengirim ?? 'Customer' }}</title>
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
                filename: 'Kuitansi-Kolektif.pdf',
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

        <!-- KUITANSI LAYOUT -->
        <div class="text-center py-1 mb-1">
            <h5 class="underline uppercase font-bold tracking-[5px] text-lg">KUITANSI</h5>
        </div>

        <div class="px-5 text-xs">
             <!-- Nomor -->
            <div class="flex mb-1 text-xs">
                <div class="w-3/12 flex justify-between pr-4"><span>Nomor</span><span>:</span></div>
                <div class="w-9/12">400.7.5./{{ $range }}/K/LL/05.3.1/{{ now()->format('Y') }}</div>
            </div>

            <!-- Tanggal Pendaftaran -->
            <div class="flex mb-1 text-xs">
                <div class="w-3/12 flex justify-between pr-4"><span>Tanggal Pendaftaran</span><span>:</span></div>
                <div class="w-9/12">
                     {{ now()->locale('id')->translatedFormat('d F Y') }}
                </div>
            </div>

            <!-- Telah Diterima Dari -->
            <div class="flex mb-1 text-xs">
                <div class="w-3/12 flex justify-between pr-1"><span>Telah diterima dari</span><span>:</span></div>
                <div class="w-9/12 uppercase font-bold">
                    {{ $first->pendaftar->nama_pengirim ?? '-' }}
                </div>
            </div>
            <div class="flex mb-1 text-xs">
                <div class="w-3/12 flex justify-between pr-1"></div>
                <div class="w-9/12">
                    {{ $first->pendaftar->alamat_pengirim ?? '-' }}
                </div>
            </div>

             <!-- Pembayaran Untuk -->
             <div class="flex mb-1 text-xs">
                <div class="w-3/12 flex justify-between pr-1"><span>Untuk Pembayaran</span><span>:</span></div>
                <div class="w-9/12">
                     Pemeriksaan Laboratorium Lingkungan {{ $first->pendaftar->jenisSampel->nama_sampel ?? '-' }}
                     <br>
                     <span class="text-[12px] italic">
                         {{ $parameterString }}
                     </span>
                     <br>
                     <!-- List Titik Sampling Compact -->
                     <div class="mt-1">
                        Titik Sampling:
                        <ul class="list-disc pl-4 mt-0.5">
                            @foreach($records as $row)
                                <li>{{ $row->pendaftar->titik_sampling }}</li>
                            @endforeach
                        </ul>
                     </div>
                </div>
            </div>

            <!-- Sejumlah Uang -->
            <div class="flex mb-1 mt-2 text-xs">
                <div class="w-3/12 flex justify-between pr-1"><span>Sejumlah Uang</span><span>:</span></div>
                <div class="w-9/12 font-bold">
                   Rp {{ number_format($totalHarga ?? 0, 0, ',', '.') }}
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
         <div class="mt-2 pt-2 lg:mt-8">
            <div class="grid grid-cols-2 gap-4">
                <div></div>
                <div class="text-center text-xs">
                    <p class="mb-0">Sragen, {{ $first->tanggal_bayar ? \Carbon\Carbon::parse($first->tanggal_bayar)->locale('id')->translatedFormat('d F Y') : 'Belum Bayar' }}</p>
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
    </div>
</body>
</html>
