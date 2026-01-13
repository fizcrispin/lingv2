@php
    // --- KONFIGURASI KERTAS & MARGIN (Hasil Lab Bulk) ---
    $paperSize = '215mm 330mm'; // Ukuran Kertas (F4)
    $pageMargin = '1.5cm';       // Margin Halaman (Cetak)
    $bodyPadding = '2rem';       // Padding Konten dalam kertas (Tampilan Layar)
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Massal Hasil Lab - {{ count($printData) }} Data</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            color: black; 
            background: #f3f4f6; 
            -webkit-print-color-adjust: exact; 
            font-size: 11pt;
            line-height: 1.3;
        }

        .no-print-wrapper { 
            text-align: center; 
            margin-bottom: 20px; 
        }

        /* Signature Block Protection */
        .signature-block {
            page-break-inside: avoid;
            break-inside: avoid;
        }

        @media print {
            .no-print-wrapper { 
                display: none; 
            }
            body { 
                padding: 0 !important; 
                margin: 0 !important; 
                background: white;
            }
            @page { 
                size: {{ $paperSize }}; 
                margin: {{ $pageMargin }}; 
            }
            
            .page-break {
                page-break-after: always;
            }
        }
        
        /* Custom Table Styles */
        table { border-collapse: collapse; width: 100%; }
        th, td { padding: 2px 4px; }
        th { text-align: center; background-color: white; }
    </style>
</head>
<body style="padding: {{ $bodyPadding }}">
    <!-- Buttons -->
    <div class="no-print-wrapper mt-3 flex justify-center gap-4 mb-8 print:hidden">
        <button class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 font-sans font-bold flex items-center gap-2" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
            </svg>
            Cetak Semua
        </button>
        <button class="bg-gray-500 text-white px-4 py-2 rounded shadow hover:bg-gray-600 font-sans font-bold" onclick="window.close()">
            Tutup
        </button>
    </div>

    @foreach($printData as $data)
        @php
            $record = $data['record'];
            $groupedResults = $data['groupedResults'];
            // $tglTerima = $data['tglTerima'];
            // $tglUji = $data['tglUji'];
        @endphp

        <!-- Main Paper Content Wrapper -->
        <div class="max-w-[215mm] mx-auto bg-white p-0 print:max-w-none {{ !$loop->last ? 'page-break mb-8 print:mb-0' : '' }} shadow-md print:shadow-none" style="position: relative;">
            
            <!-- HEADER -->
            <div class="relative border-b-[3px] border-black pb-0 mb-0">
                <!-- Left Logo -->
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/d/d3/Seal_of_Sragen_Regency.svg/250px-Seal_of_Sragen_Regency.svg.png" alt="Logo" class="absolute left-0 top-0 h-[80px] object-contain">
                
                <!-- Right Logo -->
                <img src="https://labkesjabar.com/assets/img/logokalk.png" alt="Logo" class="absolute right-0 top-0 h-[85px] object-contain">

                <div class="w-full text-center px-24">
                    <h5 class="uppercase font-bold text-[14px] mb-[2px] leading-tight">Pemerintah Kabupaten Sragen</h5>
                    <h5 class="uppercase font-bold text-[14px] mb-[2px] leading-tight">Dinas Kesehatan</h5>
                    <h4 class="font-bold text-[16px] mb-[2px] leading-tight">UPTD LABORATORIUM KESEHATAN</h4>
                    <small class="block text-[11px] leading-tight">Jalan Jend. Ahmad Yani, Sragen, Jawa Tengah 57216</small>
                    <small class="block text-[11px] leading-tight">Telepon (0271) 891078, Laman labkesda.sragenkab.go.id, Pos-el labkesda.sragen@gmail.com</small>
                </div>
            </div>

            <!-- TITLE -->
            <div class="text-center py-2 mb-1">
                <h4 class="uppercase font-bold underline text-[15px]">Hasil Pemeriksaan Laboratorium</h4>
            </div>

            <!-- INFO SECTION -->
            <div class="px-2 mb-0 text-[12px]">
                <!-- Row 1: Nomor -->
                <div class="flex mb-0 text-[12px]">
                    <div class="w-2/12 flex justify-between pr-2"><span>Nomor</span><span>:</span></div>
                    <div class="w-9/12 pl-2">400.7.5./{{ $record->no_pendaftar ?? '-' }}/H/LL/05.3.1/{{ $record->tanggal_pendaftar ? $record->tanggal_pendaftar->format('Y') : now()->format('Y') }}</div>
                </div>
                
                <!-- Row 2: Nama Register (Nama Pengirim)-->
                <div class="flex mb-0 text-[12px]">
                    <div class="w-2/12 flex justify-between pr-2"><span>Nama Register</span><span>:</span></div>
                    <div class="w-9/12 pl-2">{{ $record->nama_pengirim }}</div>
                </div>

                 <!-- Row 3: Alamat -->
                 <div class="flex mb-0 text-[12px]">
                    <div class="w-2/12 flex justify-between pr-2"><span>Alamat</span><span>:</span></div>
                    <div class="w-9/12 pl-2">{{ $record->alamat_pengirim }}</div>
                </div>

                <!-- Row 4: Jenis Sampel -->
                <div class="flex mb-0 text-[12px]">
                    <div class="w-2/12 flex justify-between pr-2"><span>Jenis Sampel</span><span>:</span></div>
                    <div class="w-9/12 pl-2">{{ $record->jenisSampel->nama_sampel ?? '-' }}</div>
                </div>

                <!-- Row 5: Petugas Sampling -->
                <div class="flex mb-0 text-[12px]">
                    <div class="w-2/12 flex justify-between pr-2"><span>Petugas Sampling</span><span>:</span></div>
                    <div class="w-9/12 pl-2">{{ $record->petugas_sampling ?? '-' }}</div>
                </div>

                 <!-- Row 6: Titik Sampling -->
                 <div class="flex mb-0 text-[12px]">
                    <div class="w-2/12 flex justify-between pr-2"><span>Titik Sampling</span><span>:</span></div>
                    <div class="w-9/12 pl-2">{{ $record->titik_sampling ?? '-' }}</div>
                </div>

                 <!-- Row 7: Alamat Sampling -->
                 <div class="flex mb-0 text-[12px]">
                    <div class="w-2/12 flex justify-between pr-2"><span>Alamat Sampling</span><span>:</span></div>
                    <div class="w-9/12 pl-2">{{ $record->alamat_sampling ?? '-' }}</div>
                </div>

                <!-- Row 8: Waktu Sampling -->
                <div class="flex mb-0 text-[12px]">
                    <div class="w-2/12 flex justify-between pr-2"><span>Waktu Sampling</span><span>:</span></div>
                    <div class="w-9/12 pl-2">{{ $record->tanggal_sampling ? \Carbon\Carbon::parse($record->tanggal_sampling)->locale('id')->translatedFormat('d F Y') : '-' }} {{ $record->waktu_sampling ? \Carbon\Carbon::parse($record->waktu_sampling)->format('H.i') . ' WIB' : '' }}</div>
                </div>

                <!-- Row 9: Baku Mutu -->
                <div class="flex mb-0 text-[12px]">
                    <div class="w-2/12 flex justify-between pr-2"><span>Baku Mutu</span><span>:</span></div>
                    <div class="w-9/12 pl-2">{{ $record->regulasi->nama_regulasi ?? '-' }}</div>
                </div>

                <!-- Row 10: Keterangan -->
                <div class="flex mb-0 text-[12px]">
                    <div class="w-2/12 flex justify-between pr-2"><span>Keterangan</span><span>:</span></div>
                    <div class="w-9/12 pl-2">{{ $record->keterangan ?? '-' }}</div>
                </div>
            </div>

            <!-- TABLE SECTION -->
            <div class="py-2 mb-0" style="padding-top: 0.5rem !important; padding-bottom: 1rem !important;">
                <table class="w-full text-[12px] border-collapse" style="border: 1px solid black;">
                    <thead class="bg-gray-300 print:bg-gray-300">
                        <tr style="color: black !important;">
                            <th class="py-1 px-1 border-b-[1px] border-black w-[5%] text-center align-middle font-bold text-[14px]">No</th>
                            <th class="py-1 px-1 border-b-[1px] border-black w-[28%] text-center align-middle font-bold text-[14px]">Parameter</th>
                            <th class="py-1 px-1 border-b-[1px] border-black w-[17%] text-center align-middle font-bold text-[14px]">Hasil</th>
                            <th class="py-1 px-1 border-b-[1px] border-black w-[15%] text-center align-middle font-bold text-[14px]" style="text-wrap: balance;">Baku Mutu</th>
                            <th class="py-1 px-1 border-b-[1px] border-black w-[14%] text-center align-middle font-bold text-[14px]">Satuan</th>
                            <th class="py-1 px-1 border-b-[1px] border-black w-[21%] text-center align-middle font-bold text-[14px]">Metode</th>
                        </tr>
                    </thead>
                    <tbody style="color: black !important;">
                        @forelse($groupedResults as $kategori => $items)
                            <!-- Category Header -->
                            <tr class="border-b border-gray-400">
                                <td colspan="6" class="py-1 px-2 font-bold uppercase text-left align-middle border-b border-gray-500 text-black">
                                    {{ $kategori }}
                                </td>
                            </tr>
                            
                            @php $idx = 1; @endphp
                            @foreach($items as $hasil)
                            <tr class="border-b border-gray-500">
                                <td class="py-0 px-0 text-center align-middle text-[12px]">{{ $idx++ }}</td>
                                <td class="py-0 px-0 align-middle text-[12px] pl-4">{{ $hasil->nama_parameter }}</td>
                                <td class="py-0 px-0 text-center align-middle font-bold text-[12px]">{{ $hasil->hasil_parameter ?? '' }}</td>
                                <td class="py-0 px-0 text-center align-middle text-[12px]">{{ $hasil->parameter->batas_max ?? '' }}</td>
                                <td class="py-0 px-0 text-center align-middle text-[12px]">{{ $hasil->parameter->satuan ?? '' }}</td>
                                <td class="py-0 px-0 text-center align-middle text-[12px]">{{ $hasil->parameter->metode_pemeriksaan ?? '' }}</td>
                            </tr>
                            @endforeach
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 italic text-gray-500">Belum ada hasil pengujian yang diinput.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- NOTES SECTION -->
            <div class="text-[12px] mb-1">
                <span class="font-bold mb-1 block">CATATAN :</span>
                <ol class="list-decimal pl-5 space-y-1">
                    <li>Hasil Pemeriksaan hanya berlaku untuk parameter yang dilaksanakan pengujian laboratorium.</li>
                    <li>Dilarang menggandakan dokumen ini sebagai laporan hasil pengujian tanpa persetujuan dari UPTD Laboratorium Kesehatan Kabupaten Sragen.</li>
                </ol>
            </div>

            <!-- FOOTER SIGNATURES WRAPPED -->
            <div class="signature-block pt-4 text-[13px]">
                <!-- Date Row -->
                <div class="grid grid-cols-2 mb-2">
                    <div></div> <!-- Spacer -->
                    <div class="text-center">
                        <span>
                            @if($record->hasilLingkungans->max('tanggal_input'))
                                Sragen, {{ \Carbon\Carbon::parse($record->hasilLingkungans->max('tanggal_input'))->locale('id')->translatedFormat('d F Y') }}
                            @else
                                Tanggal Input Hasil Belum dimasukan
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Titles -->
                 <div class="grid grid-cols-2 text-center mb-14">
                    <div>
                        <p class="font-bold ">Kepala UPTD Laboratorium Kesehatan</p>
                        <p>Kabupaten Sragen</p>
                    </div>
                    <div>
                        <p class="font-bold">Penanggung Jawab</p>
                        <p>Laboratorium Lingkungan</p>
                    </div>
                </div>

                <!-- Names & NIPs -->
                <div class="grid grid-cols-2 text-center">
                    <!-- Kepala UPTD -->
                    <div>
                         <p class="font-bold underline mb-0">SISWIYARDI, S.K.M., M.M.</p>
                         <p>NIP. 19721023 199503 1 002</p>
                    </div>
                    <!-- PJ Lab -->
                    <div>
                        <p class="font-bold underline mb-0">HAFIZ FAUZI, A.Md.Kes.</p>
                        <p>NIP. 19980524 202321 1 002</p>
                   </div>
                </div>
            </div>

        </div>
    @endforeach
</body>
</html>
