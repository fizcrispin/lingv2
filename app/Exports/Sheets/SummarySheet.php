<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SummarySheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize, WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
        $this->d = $data;
    }

    public function array(): array
    {
        $d = $this->data;
        $rows = [];

        // --- DASHBOARD LAYOUT (Grid Simulation) ---
        // Col: A(Spacer), B(Label), C(Val), D(Spacer), E(Label), F(Val), G(Spacer) ...

        // Header Title (Row 2) - Merged Later
        $rows[] = ['']; // 1 Spacer
        $rows[] = ['', 'EXECUTIVE DASHBOARD REPORT BY HAFIZ EANG CELALUE TERCAKYTI']; // 2
        $rows[] = ['', 'Periode: ' . $d['start_date'] . ' s/d ' . $d['end_date']]; // 3
        $rows[] = ['']; // 4 Spacer

        // SECTION 1: KEY METRICS (Row 5)
        $rows[] = [
            '', 
            'TOTAL PENDAFTARAN', '', '', // Card 1 (B-C)
            'TOTAL PENDAPATAN', '', '', // Card 2 (E-F)
            'SAMPEL SELESAI', '', '', // Card 3 (H-I)
        ]; // 5 Header

        $rows[] = [
            '', 
            $d['pendaftaran_count'] . ' Dokumen', '', '', // Val 1
            'IDR ' . number_format($d['total_revenue'], 0, ',', '.'), '', '', // Val 2
            ($d['lab_input_stats']['total_sampel_input'] ?? 0) . ' Sampel', '', '', // Val 3
        ]; // 6 Value

        $rows[] = ['']; // 7 Spacer

        // SECTION 2: DETAILED STATS (Row 8 Headers)
        $rows[] = [
            '',
            'STATUS EKSPEDISI', '', '',
            'METODE PEMBAYARAN', '', '',
            'PROGRESS LABORATORIUM', '', '',
        ]; // 8 Headers
        
        // Prepare Data Lists for Side-by-Side
        $ekspedisiRows = [];
        foreach (($d['ekspedisi_stats'] ?? []) as $k => $v) $ekspedisiRows[] = [$k, $v];
        
        $paymentRows = [];
        foreach (($d['payment_method_stats'] ?? []) as $k => $v) $paymentRows[] = [$k ?? 'N/A', $v];
        
        $lab = $d['lab_input_stats'] ?? [];
        $labRows = [
            ['Total Sampel', $lab['total_sampel_input'] ?? 0],
            ['Total Parameter', $lab['total_parameter_input'] ?? 0],
            ['Sudah Input (Sampel)', count($lab['jenis_sampel_sudah'] ?? [])],
            ['Belum Input (Sampel)', count($lab['jenis_sampel_belum'] ?? [])],
        ];

        // Merge Side-by-Side (Max Rows)
        $max = max(count($ekspedisiRows), count($paymentRows), count($labRows));
        
        for ($i = 0; $i < $max; $i++) {
            $e = $ekspedisiRows[$i] ?? ['', ''];
            $p = $paymentRows[$i] ?? ['', ''];
            $l = $labRows[$i] ?? ['', ''];
            
            $rows[] = [
                '',
                $e[0], $e[1], '',
                $p[0], $p[1], '',
                $l[0], $l[1], '',
            ];
        }

        $rows[] = ['']; // Spacer

        // SECTION 3: EXTRA STATS (Row Dynamic)
        $rows[] = [
            '',
            'DISTRIBUSI JENIS SAMPEL', '', '',
            'STATISTIK PARAMETER (TOTAL)', '', '',
        ]; 

        $jenisRows = [];
        foreach (($d['jenis_sampel_stats'] ?? []) as $k => $v) $jenisRows[] = [$k, $v];

        $paramRows = [];
        foreach (($d['parameter_stats'] ?? []) as $k => $v) $paramRows[] = [$k, $v];

        $max3 = max(count($jenisRows), count($paramRows));

        for ($i = 0; $i < $max3; $i++) {
            $j = $jenisRows[$i] ?? ['', ''];
            $p = $paramRows[$i] ?? ['', ''];
            
            $rows[] = [
                '',
                $j[0], $j[1], '',
                $p[0], $p[1], '',
            ];
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $e = $event->sheet->getDelegate();
                
                // 1. Title Styling
                $e->mergeCells('B2:I2'); // Title
                $e->mergeCells('B3:I3'); // Subtitle
                $e->getStyle('B2')->getFont()->setSize(24)->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3A8A'));
                $e->getStyle('B3')->getFont()->setSize(12)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF6B7280'));
                $e->getStyle('B2:B3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 2. High Level Cards Styling (Row 5-6)
                // Card 1: B5:C6
                $styleCard = function($range, $color) use ($e) {
                    $e->mergeCells(substr($range, 0, 2) . substr($range, 3, 1)); // Merge Header Row
                    $e->mergeCells(substr($range, 0, 1) . (intval(substr($range, 1))+1) . ':' . substr($range, 3)); // Merge Value Row
                    
                    $e->getStyle($range)->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $color]],
                        'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FFFFFFFF']]], // White outline
                        'font' => ['color' => ['argb' => 'FFFFFFFF']]
                    ]);
                    // Header font
                    $e->getStyle(substr($range, 0, 2) . substr($range, 3, 1))->getFont()->setBold(true)->setSize(10);
                     // Value font
                    $e->getStyle(substr($range, 0, 1) . (intval(substr($range, 1))+1))->getFont()->setBold(true)->setSize(16);
                    $e->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                };

                // Pendaftar (Blue)
                $e->mergeCells('B5:C5'); $e->mergeCells('B6:C6');
                $e->getStyle('B5:C6')->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF3B82F6']]]);
                $e->getStyle('B5')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
                $e->getStyle('B6')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
                $e->getStyle('B5:C6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Pendapatan (Green)
                $e->mergeCells('E5:F5'); $e->mergeCells('E6:F6');
                $e->getStyle('E5:F6')->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF10B981']]]);
                $e->getStyle('E5')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
                $e->getStyle('E6')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
                $e->getStyle('E5:F6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Sampel (Purple)
                $e->mergeCells('H5:I5'); $e->mergeCells('H6:I6');
                $e->getStyle('H5:I6')->applyFromArray(['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF8B5CF6']]]);
                $e->getStyle('H5')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
                $e->getStyle('H6')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
                $e->getStyle('H5:I6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);


                // 3. Detail Tables Styling (Row 8+)
                $headers = ['B8:C8', 'E8:F8', 'H8:I8'];
                foreach($headers as $h) {
                    $e->mergeCells($h);
                    $e->getStyle($h)->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FF1F2937']],
                        'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['argb' => 'FF3B82F6']]],
                    ]);
                    $e->getStyle($h)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Global Column Widths
                $e->getColumnDimension('A')->setWidth(2);
                $e->getColumnDimension('B')->setWidth(25);
                $e->getColumnDimension('C')->setWidth(15);
                $e->getColumnDimension('D')->setWidth(2);
                $e->getColumnDimension('E')->setWidth(25);
                $e->getColumnDimension('F')->setWidth(15);
                $e->getColumnDimension('G')->setWidth(2);
                $e->getColumnDimension('H')->setWidth(25);
                $e->getColumnDimension('I')->setWidth(15);
                
                // 4. Style Section 3 (Extra Stats)
                // Calculate dynamic start row for Section 3 based on Section 2 data
                // Section 2 starts at Row 8.
                // We need to pass the row count to usage, but here in styling we might need to recalculate or store it.
                // Since registerEvents doesn't easily share state with array(), we'll calculate the row offset again or be dynamic.
                // However, an easier way is to style "All cells with borders" or specific ranges if we know them.
                // But for simplicity, let's just Apply borders to the specific Header Row of Section 3 and the data below it.
                
                // Finding the "DISTRIBUSI JENIS SAMPEL" header to identify start of Section 3
                // We can't search easily.
                // Alternative: Use strict row numbers calculated from data size.
                $d = $event->getConcernable()->d; // Access data via public property created in constructor
                
                $ekspedisiCount = count($d['ekspedisi_stats'] ?? []);
                $paymentCount = count($d['payment_method_stats'] ?? []);
                $labRowsCount = 4; // Fixed
                
                $sec2Height = max($ekspedisiCount, $paymentCount, $labRowsCount);
                $sec3HeaderRow = 8 + $sec2Height + 2; // 8 (Start Sec2) + Height + 2 (Spacer)
                
                $headers3 = ['B'.$sec3HeaderRow.':C'.$sec3HeaderRow, 'E'.$sec3HeaderRow.':F'.$sec3HeaderRow];
                foreach($headers3 as $h) {
                    $e->mergeCells($h);
                    $e->getStyle($h)->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => 'FF1F2937']],
                        'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THICK, 'color' => ['argb' => 'FF8B5CF6']]], // Purple border
                    ]);
                    $e->getStyle($h)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
            }
        ];
    }
    
    // Public property to access data in registerEvents
    public $d; 

    
    // Stub required methods
    public function styles(Worksheet $sheet) { return []; } 
    public function title(): string { return 'Ringkasan Eksekutif'; }
}
