<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use App\Models\PendaftarLingkungan;
use App\Models\InvoiceLingkungan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DailySheet implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithEvents, WithCustomStartCell
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // 1. Add Top Group Headers
                $sheet->setCellValue('A1', 'UMUM'); 
                $sheet->mergeCells('A1:B1');
                
                $sheet->setCellValue('C1', 'STATUS EKSPEDISI BY HAFIZ EANG CELALUE TERCAKYTI'); 
                $sheet->mergeCells('C1:H1');
                
                $sheet->setCellValue('I1', 'PROGRESS LAB'); 
                $sheet->mergeCells('I1:K1');
                
                $sheet->setCellValue('L1', 'RINCIAN JENIS SAMPEL'); 
                $sheet->mergeCells('L1:S1');
                
                $sheet->setCellValue('T1', 'KEUANGAN'); 
                $sheet->mergeCells('T1:V1');

                // 2. Style Top Headers (Row 1)
                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4B5563']], // Dark Gray
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFFFFFF']]],
                ];
                $sheet->getStyle('A1:V1')->applyFromArray($headerStyle);
                
                // Specific Colors for Groups
                $sheet->getStyle('C1:H1')->getFill()->getStartColor()->setARGB('FFD97706'); // Orange (Ekspedisi)
                $sheet->getStyle('I1:K1')->getFill()->getStartColor()->setARGB('FF2563EB'); // Blue (Lab)
                $sheet->getStyle('L1:S1')->getFill()->getStartColor()->setARGB('FF059669'); // Green (Sampel)
                $sheet->getStyle('T1:V1')->getFill()->getStartColor()->setARGB('FF7C3AED'); // Purple (Keuangan)

                // 3. Freeze Panes
                $sheet->freezePane('C3'); // Freeze Columns A-B and Rows 1-2

                // 4. Style Data Rows (Zebra Striping)
                $highestRow = $sheet->getHighestRow();
                for ($row = 3; $row <= $highestRow; $row++) {
                    // Total Row Styling (Last Row)
                    if ($row == $highestRow) {
                        $sheet->getStyle("A{$row}:V{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F2937']], // Very Dark (Footer)
                        ]);
                        continue;
                    }

                    // Zebra
                    if ($row % 2 == 0) {
                        $sheet->getStyle("A{$row}:V{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB('FFF3F4F6'); // Light Gray
                    }
                    
                    // Borders for all cells
                    $sheet->getStyle("A{$row}:V{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFD1D5DB'));
                }

                // 5. Center Align SubHeaders (Row 2) and Data
                $sheet->getStyle('A2:V2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A2:V2')->getFont()->setBold(true);
                $sheet->getStyle('A2:V2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE5E7EB'); // Gray 200

                // Center specific columns content
                $sheet->getStyle('C3:S'.$highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }

    public function collection()
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        
        $dates = [];
        $current = $start->copy();
        
        while ($current <= $end) {
            $dates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        // Pre-fetch Data
        $pendaftars = PendaftarLingkungan::whereDate('tanggal_pendaftar', '>=', $this->startDate)
            ->whereDate('tanggal_pendaftar', '<=', $this->endDate)
            ->with(['jenisSampel', 'ekspedisi', 'hasilLingkungans', 'invoice'])
            ->get()
            ->groupBy(fn($item) => Carbon::parse($item->tanggal_pendaftar)->format('Y-m-d'));

        $rows = collect();

        foreach ($dates as $date) {
            $dayRecords = $pendaftars->get($date, collect());
            
            // 1. Ekspedisi Stats
            $ekspedisi = [
                'belum_diterima' => $dayRecords->where(fn($p) => optional($p->ekspedisi)->sampel_diterima == 0)->count(),
                'sudah_diterima' => $dayRecords->where(fn($p) => optional($p->ekspedisi)->sampel_diterima == 1)->count(),
                'verif' => $dayRecords->where(fn($p) => optional($p->ekspedisi)->verifikasi_hasil == 1)->count(),
                'valid1' => $dayRecords->where(fn($p) => optional($p->ekspedisi)->validasi1 == 1)->count(),
                'valid2' => $dayRecords->where(fn($p) => optional($p->ekspedisi)->validasi2 == 1)->count(),
                'musnah' => $dayRecords->where(fn($p) => optional($p->ekspedisi)->sampel_dimusnahkan == 1)->count(),
            ];

            // 2. Lab Input Stats (General)
            $totalParam = 0;
            $filledParam = 0;
            $pendaftarSelesai = 0;

            foreach ($dayRecords as $p) {
                $params = $p->hasilLingkungans;
                $pCount = $params->count();
                $pFilled = $params->whereNotNull('hasil_parameter')->where('hasil_parameter', '!=', '')->count();
                
                $totalParam += $pCount;
                $filledParam += $pFilled;

                if ($pCount > 0 && $pCount == $pFilled) {
                    $pendaftarSelesai++;
                }
            }

            // 3. Progress per Jenis Sampel
            // Helper function
            $getStatsByType = function($type) use ($dayRecords) {
                $filtered = $dayRecords->filter(fn($p) => optional($p->jenisSampel)->nama_sampel === $type);
                $total = $filtered->count();
                $selesai = $filtered->filter(function($p) {
                    $params = $p->hasilLingkungans;
                    return $params->isNotEmpty() && $params->every(fn($h) => !empty($h->hasil_parameter));
                })->count();
                return ['total' => $total, 'selesai' => $selesai, 'belum' => $total - $selesai];
            };

            $limbah = $getStatsByType('Air Limbah');
            $bersih = $getStatsByType('Air Bersih');
            $minum = $getStatsByType('Air Minum');
            
            // Lainnya
            $lainnyaFiltered = $dayRecords->filter(function($p) {
                $name = optional($p->jenisSampel)->nama_sampel;
                return !in_array($name, ['Air Limbah', 'Air Bersih', 'Air Minum']);
            });
            $lainnyaTotal = $lainnyaFiltered->count();
            $lainnyaSelesai = $lainnyaFiltered->filter(function($p) {
                $params = $p->hasilLingkungans;
                return $params->isNotEmpty() && $params->every(fn($h) => !empty($h->hasil_parameter));
            })->count();
            $lainnya = ['total' => $lainnyaTotal, 'selesai' => $lainnyaSelesai, 'belum' => $lainnyaTotal - $lainnyaSelesai];

            // 4. Keuangan (Aggregated from Invoice relation to ensure sync with pendaftar day)
            // Note: User logic originally grouped invoices by invoice date. 
            // BUT "Daily Report" for operations usually tracks "Money for samples registered today" or "Money received today"?
            // Usually Key Performance is "Receipts Today". 
            // Let's stick to the previous Invoice logic (grouped by Invoice Date) OR align it with Pendaftar?
            // "row paling bawah merupakan sum nya" implies a single table.
            // If we use Invoice Date, it might not match Pendaftar Date rows if we merge them.
            // However, typical daily reports show "Activities on this day".
            // So: Pendaftar = Registered on Day X. Invoice = Bill created/Paid on Day X.
            // I will re-query Invoices by date to match the "Activity Date" concept.
            
            // Re-fetch invoices specifically for this date to be accurate to "Finance Activity"
            // (Already fetched in previous implementation, just need to keep that logic)
            // Wait, previous implementation passed $invoices grouped by date. I should re-use that efficiently.
            
            // Optimized: I'll fetch invoices inside the loop (or pre-fetch like before)
            // Let's stick to pre-fetching like before for performance.
        }
        
        // RE-FETCHING INVOICES (Restoring logic)
        $invoices = InvoiceLingkungan::whereDate('tanggal_tagihan', '>=', $this->startDate)
            ->whereDate('tanggal_tagihan', '<=', $this->endDate)
            ->get()
            ->groupBy(fn($item) => Carbon::parse($item->tanggal_tagihan)->format('Y-m-d'));

        foreach ($dates as $date) {
             // ... previous logic ...
             // Re-instating $dayRecords and stats
        }
        
        // TO KEEP CODE CLEAN, I WILL REWRITE THE LOOP COMPLETELY IN THE REPLACEMENT CONTENT
        
        return $this->generateCollection($dates, $pendaftars, $invoices);
    }

    protected function generateCollection($dates, $pendaftars, $invoices) {
        $rows = collect();

        foreach ($dates as $date) {
            $dayRecords = $pendaftars->get($date, collect());
            $dayInvoices = $invoices->get($date, collect());

            // Ekspedisi
            $ekspedisi = [
                'belum_diterima' => $dayRecords->where(fn($p) => optional($p->ekspedisi)->sampel_diterima == 0)->count(),
                'sudah_diterima' => $dayRecords->where(fn($p) => optional($p->ekspedisi)->sampel_diterima == 1)->count(),
                'verif' => $dayRecords->where(fn($p) => optional($p->ekspedisi)->verifikasi_hasil == 1)->count(),
                'valid1' => $dayRecords->where(fn($p) => optional($p->ekspedisi)->validasi1 == 1)->count(),
                'valid2' => $dayRecords->where(fn($p) => optional($p->ekspedisi)->validasi2 == 1)->count(),
                'musnah' => $dayRecords->where(fn($p) => optional($p->ekspedisi)->sampel_dimusnahkan == 1)->count(),
            ];

            // Lab Stats
            $totalParam = 0; $filledParam = 0; $pendaftarSelesai = 0;
            foreach ($dayRecords as $p) {
                $params = $p->hasilLingkungans;
                $pCount = $params->count();
                $pFilled = $params->whereNotNull('hasil_parameter')->where('hasil_parameter', '!=', '')->count();
                $totalParam += $pCount;
                $filledParam += $pFilled;
                if ($pCount > 0 && $pCount == $pFilled) $pendaftarSelesai++;
            }

            // Stats Helper
            $calc = fn($type) => [
                'total' => $dayRecords->filter(fn($p) => optional($p->jenisSampel)->nama_sampel === $type)->count(),
                'selesai' => $dayRecords->filter(fn($p) => optional($p->jenisSampel)->nama_sampel === $type && $p->hasilLingkungans->isNotEmpty() && $p->hasilLingkungans->every(fn($h) => !empty($h->hasil_parameter)))->count()
            ];
            
            $limbah = $calc('Air Limbah');
            $bersih = $calc('Air Bersih');
            $minum = $calc('Air Minum');
            
            // Lainnya
            $lainnyaFiltered = $dayRecords->filter(fn($p) => !in_array(optional($p->jenisSampel)->nama_sampel, ['Air Limbah', 'Air Bersih', 'Air Minum']));
            $lainnya = [
                'total' => $lainnyaFiltered->count(),
                'selesai' => $lainnyaFiltered->filter(fn($p) => $p->hasilLingkungans->isNotEmpty() && $p->hasilLingkungans->every(fn($h) => !empty($h->hasil_parameter)))->count()
            ];


            $row = [
                'date' => $date,
                // Pendaftaran
                'total_pendaftar' => $dayRecords->count(),
                // Ekspedisi
                'eks_belum' => $ekspedisi['belum_diterima'],
                'eks_terima' => $ekspedisi['sudah_diterima'],
                'eks_verif' => $ekspedisi['verif'],
                'eks_valid1' => $ekspedisi['valid1'],
                'eks_valid2' => $ekspedisi['valid2'],
                // Lab Input
                'lab_selesai_dok' => $pendaftarSelesai,
                'lab_total_param' => $totalParam,
                'lab_isi_param' => $filledParam,
                // Progress Jenis Sampel (Total / Selesai)
                'limbah_total' => $limbah['total'], 'limbah_ok' => $limbah['selesai'],
                'bersih_total' => $bersih['total'], 'bersih_ok' => $bersih['selesai'],
                'minum_total' => $minum['total'], 'minum_ok' => $minum['selesai'],
                'lain_total' => $lainnya['total'], 'lain_ok' => $lainnya['selesai'],
                // Keuangan
                'rev_total' => $dayInvoices->sum('total_bayar'),
                'rev_paid' => $dayInvoices->where('status_bayar', 2)->count(),
                'rev_unpaid' => $dayInvoices->where('status_bayar', '!=', 2)->count(),
            ];
            $rows->push($row);
        }

        // Summary
        $sum = fn($key) => $rows->sum($key);
        $summary = [
            'date' => 'TOTAL',
            'total_pendaftar' => $sum('total_pendaftar'),
            'eks_belum' => $sum('eks_belum'), 'eks_terima' => $sum('eks_terima'), 'eks_verif' => $sum('eks_verif'),
            'eks_valid1' => $sum('eks_valid1'), 'eks_valid2' => $sum('eks_valid2'),
            'lab_selesai_dok' => $sum('lab_selesai_dok'), 'lab_total_param' => $sum('lab_total_param'), 'lab_isi_param' => $sum('lab_isi_param'),
            'limbah_total' => $sum('limbah_total'), 'limbah_ok' => $sum('limbah_ok'),
            'bersih_total' => $sum('bersih_total'), 'bersih_ok' => $sum('bersih_ok'),
            'minum_total' => $sum('minum_total'), 'minum_ok' => $sum('minum_ok'),
            'lain_total' => $sum('lain_total'), 'lain_ok' => $sum('lain_ok'),
            'rev_total' => $sum('rev_total'), 'rev_paid' => $sum('rev_paid'), 'rev_unpaid' => $sum('rev_unpaid'),
        ];
        $rows->push($summary);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Total Pendaftar',
            // Ekspedisi
            'Blm Terima', 'Diterima', 'Verifikasi', 'Validasi 1', 'Validasi 2',
            // Lab
            'Dok. Selesai', 'Tot. Param', 'Param. Isi',
            // Limbah
            'Limbah (T)', 'Limbah (OK)',
            // Bersih
            'Bersih (T)', 'Bersih (OK)',
            // Minum
            'Minum (T)', 'Minum (OK)',
            // Lain
            'Lain (T)', 'Lain (OK)',
            // Keuangan
            'Pendapatan', 'Lunas', 'Belum',
        ];
    }

    public function map($row): array
    {
        return array_values($row);
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:U1')->getFont()->setBold(true); // Header
        $sheet->getStyle('A'.$lastRow.':U'.$lastRow)->getFont()->setBold(true);
        $sheet->getStyle('A'.$lastRow.':U'.$lastRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
        
        return [];
    }

    public function title(): string
    {
        return 'Data Harian Lengkap';
    }
}
