<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use App\Models\PendaftarLingkungan;
use App\Models\Ekspedisi;
use App\Models\InvoiceLingkungan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use BackedEnum;

class LaporanPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static string|\UnitEnum|null $navigationGroup = 'Laporan';
    protected static ?string $navigationLabel = 'Dashboard Laporan';
    protected static ?string $title = 'Laporan Operasional & Keuangan';
    protected string $view = 'filament.pages.laporan-page';

    // Filters
    public ?string $filter_period = 'today';
    public ?string $start_date = null;
    public ?string $end_date = null;

    // Data Pendaftaran
    public $pendaftaran_count = 0;
    public $jenis_sampel_stats = [];
    public $parameter_stats = [];

    // Data Ekspedisi
    public $ekspedisi_stats = [];
    public $avg_verifikasi_time = '0 Hari';

    // Data Keuangan
    public $total_revenue = 0;
    public $total_paid = 0;
    public $total_unpaid = 0;
    public $payment_method_stats = [];

    public function mount()
    {
        $this->filter_period = 'today';
        $this->updateDates();
        $this->loadData();
    }

    public function form(\Filament\Schemas\Schema $schema): \Filament\Schemas\Schema
    {
        return $schema
            ->components([
                Section::make('Filter Laporan') -> schema([
                        Select::make('filter_period')
                            ->label('Periode')
                            ->options([
                                'today' => 'Hari Ini',
                                'week' => 'Minggu Ini',
                                'month' => 'Bulan Ini',
                                '6month' => '6 Bulan Terakhir',
                                'year' => 'Tahun Ini',
                                'custom' => 'Custom Range',
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->updateDates();
                                $this->loadData();
                            }),
                        DatePicker::make('start_date')
                            ->label('Dari Tanggal')
                            ->visible(fn () => $this->filter_period === 'custom')
                            ->live()
                            ->afterStateUpdated(fn () => $this->loadData()),
                        DatePicker::make('end_date')
                            ->label('Sampai Tanggal')
                            ->visible(fn () => $this->filter_period === 'custom')
                            ->live()
                            ->afterStateUpdated(fn () => $this->loadData()),
                    ])->columns(3)
            ]);
    }

    public function updateDates()
    {
        $now = Carbon::now();
        switch ($this->filter_period) {
            case 'today':
                $this->start_date = $now->format('Y-m-d');
                $this->end_date = $now->format('Y-m-d');
                break;
            case 'week':
                $this->start_date = $now->startOfWeek()->format('Y-m-d');
                $this->end_date = $now->endOfWeek()->format('Y-m-d');
                break;
            case 'month':
                $this->start_date = $now->startOfMonth()->format('Y-m-d');
                $this->end_date = $now->endOfMonth()->format('Y-m-d');
                break;
            case '6month':
                $this->start_date = $now->subMonths(6)->format('Y-m-d');
                $this->end_date = now()->format('Y-m-d');
                break;
            case 'year':
                $this->start_date = $now->startOfYear()->format('Y-m-d');
                $this->end_date = $now->endOfYear()->format('Y-m-d');
                break;
            // Custom handles itself
        }
    }

    public function loadData()
    {
        $start = $this->start_date;
        $end = $this->end_date; // Inclusive need to ensure query handles up to end of day if datetime

        // 1. Pendaftaran
        $pendaftarQuery = PendaftarLingkungan::query()
            ->whereDate('tanggal_pendaftar', '>=', $start)
            ->whereDate('tanggal_pendaftar', '<=', $end);
        
        $this->pendaftaran_count = $pendaftarQuery->clone()->count();
        
        $this->jenis_sampel_stats = $pendaftarQuery->clone()->with('jenisSampel')
            ->get()
            ->groupBy('jenisSampel.nama_sampel')
            ->map->count()
            ->toArray();

        // Parameter stats calculation using Chunk on a CLONED query to avoid messing up main query
        $allParams = [];
        $pendaftarQuery->clone()->chunk(100, function($pendaftars) use (&$allParams) {
            foreach($pendaftars as $p) {
                if ($p->mode_parameter) { // Manual
                     $params = $p->parameter ?? []; 
                } else { // Paket
                     $params = $p->paket?->parameter ?? []; 
                }
                if (is_string($params)) $params = json_decode($params, true) ?? [];
                
                foreach($params as $pid) {
                    $allParams[$pid] = ($allParams[$pid] ?? 0) + 1;
                }
            }
        });
        arsort($allParams);

        $paramNames = \App\Models\ParameterLingkungan::whereIn('id', array_keys($allParams))->pluck('nama_parameter', 'id');
        $this->parameter_stats = [];
        foreach($allParams as $pid => $count) {
             if(isset($paramNames[$pid])) {
                 $this->parameter_stats[$paramNames[$pid]] = $count;
             }
        }


        // 2. Ekspedisi Stats
        $ekspedisiData = Ekspedisi::query()
            ->whereHas('pendaftarLingkungan', function($q) use ($start, $end) {
                $q->whereDate('tanggal_pendaftar', '>=', $start)
                  ->whereDate('tanggal_pendaftar', '<=', $end);
            })
            ->with(['pendaftarLingkungan.hasilLingkungans'])
            ->get();

        $this->ekspedisi_stats = [
            'Belum Diterima' => $ekspedisiData->where('sampel_diterima', 0)->count(),
            'Sudah Diterima' => $ekspedisiData->where('sampel_diterima', 1)->count(),
            'Verifikasi Hasil' => $ekspedisiData->where('verifikasi_hasil', 1)->count(),
            'Validasi 1' => $ekspedisiData->where('validasi1', 1)->count(),
            'Validasi 2' => $ekspedisiData->where('validasi2', 1)->count(),
            'Dimusnahkan' => $ekspedisiData->where('sampel_dimusnahkan', 1)->count(),
        ];
        
        // "Selesai Input" Logic
        $selesaiInputCount = 0;
        foreach($ekspedisiData as $eks) {
             $results = $eks->pendaftarLingkungan->hasilLingkungans ?? collect([]);
             if ($results->isNotEmpty() && $results->every(fn($r) => !empty($r->hasil_parameter))) {
                 $selesaiInputCount++;
             }
        }
        $this->ekspedisi_stats['Selesai Input'] = $selesaiInputCount;


        // 3. Lab Input Stats
        
        // Context A: Pendaftar in Date Range (For "Belum" counts)
        // Use CLONE of base query
        $pendaftarInRange = $pendaftarQuery->clone()->with(['jenisSampel', 'hasilLingkungans.parameter'])->get();
        
        // Context B: Input Query (Based on Pendaftar Date Range aka "Jadikan satu saja")
        $hasilBaseQuery = \App\Models\HasilLingkungan::query()
            ->whereHas('pendaftar', function($q) use ($start, $end) {
                $q->whereDate('tanggal_pendaftar', '>=', $start)
                  ->whereDate('tanggal_pendaftar', '<=', $end);
            })
            ->whereNotNull('hasil_parameter')
            ->where('hasil_parameter', '!=', '');
            
        // STAT: Total Sampel Sudah Input
        $totalSampelInputByActivity = $hasilBaseQuery->clone()->distinct('id_pendaftar')->count('id_pendaftar');
        
        // STAT: Total Parameter Sudah Input
        $totalParameterInputByActivity = $hasilBaseQuery->clone()->count();

        // STAT: Jenis Sampel - Sudah Input
        $jenisSampelSudahInput = $hasilBaseQuery->clone()->with('pendaftar.jenisSampel')
            ->get()
            ->groupBy(fn($item) => $item->pendaftar?->jenisSampel?->nama_sampel ?? 'Lainnya')
            ->map->count()
            ->toArray();
            
        // STAT: Jenis Sampel - Belum Input
        $jenisSampelBelumInput = [];
        foreach($pendaftarInRange as $p) {
            // Count pending items: NULL or Empty String
            $pendingCount = $p->hasilLingkungans->filter(function ($value) {
                return empty($value->hasil_parameter);
            })->count();

            if ($pendingCount > 0) {
                 $k = $p->jenisSampel?->nama_sampel ?? 'Lainnya';
                 $jenisSampelBelumInput[$k] = ($jenisSampelBelumInput[$k] ?? 0) + 1;
            }
        }

        // STAT: Parameter - Sudah Input
        $parameterSudahInput = $hasilBaseQuery->clone()->with('parameter') 
            ->get()
            ->groupBy(fn($item) => $item->parameter?->nama_parameter ?? 'Unknown') 
            ->map->count()
            ->toArray();

        // STAT: Parameter - Belum Input
        $parameterBelumInput = [];
         foreach($pendaftarInRange as $p) {
            foreach($p->hasilLingkungans as $hl) {
                if (empty($hl->hasil_parameter)) {
                     $pName = $hl->parameter?->nama_parameter ?? $hl->nama_parameter ?? 'Unknown';
                     $parameterBelumInput[$pName] = ($parameterBelumInput[$pName] ?? 0) + 1;
                }
            }
        }
        arsort($parameterSudahInput);
        arsort($parameterBelumInput);

        // Assign to view vars
        $this->lab_input_stats = [
             'total_sampel_input' => $totalSampelInputByActivity,
             'total_parameter_input' => $totalParameterInputByActivity,
             'jenis_sampel_sudah' => $jenisSampelSudahInput,
             'jenis_sampel_belum' => $jenisSampelBelumInput,
             'parameter_sudah' => array_slice($parameterSudahInput, 0, 20),
             'parameter_belum' => array_slice($parameterBelumInput, 0, 20),
        ];

        // 4. Time Average
        $totalDiff = 0;
        $countDiff = 0;
        foreach($ekspedisiData as $eks) {
            if ($eks->tanggal_diterima && $eks->pendaftarLingkungan?->created_at) {
                $startT = Carbon::parse($eks->pendaftarLingkungan->created_at);
                $endT = Carbon::parse($eks->tanggal_diterima);
                $totalDiff += $endT->diffInHours($startT);
                $countDiff++;
            }
        }
        $this->avg_verifikasi_time = $countDiff > 0 ? round($totalDiff / $countDiff, 1) . ' Jam' : '-';

        // 5. Transaksi
        $invQuery = InvoiceLingkungan::query()
            ->whereDate('tanggal_tagihan', '>=', $start)
            ->whereDate('tanggal_tagihan', '<=', $end);
            
        $this->total_revenue = $invQuery->sum('total_bayar'); 
        $this->total_paid = $invQuery->where('status_bayar', 2)->count(); 
        $this->total_unpaid = $invQuery->where('status_bayar', '!=', 2)->count(); 
        
        $this->payment_method_stats = $invQuery->select('metode_pembayaran', DB::raw('count(*) as total'))
            ->groupBy('metode_pembayaran')
            ->pluck('total', 'metode_pembayaran')
            ->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->exportData()),
        ];
    }

    public function exportData()
    {
        $data = [
            'start_date' => $this->start_date ?? date('Y-m-d'),
            'end_date' => $this->end_date ?? date('Y-m-d'),
            'pendaftaran_count' => $this->pendaftaran_count,
            'jenis_sampel_stats' => $this->jenis_sampel_stats,
            'parameter_stats' => $this->parameter_stats,
            'ekspedisi_stats' => $this->ekspedisi_stats,
            'avg_verifikasi_time' => $this->avg_verifikasi_time,
            'total_revenue' => $this->total_revenue,
            'total_paid' => $this->total_paid,
            'total_unpaid' => $this->total_unpaid,
            'payment_method_stats' => $this->payment_method_stats,
        ];

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanExport($data), 
            'Laporan-' . $this->filter_period . '-' . date('Y-m-d') . '.xlsx'
        );
    }
}
