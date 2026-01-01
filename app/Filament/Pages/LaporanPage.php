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
        
        $this->pendaftaran_count = $pendaftarQuery->count();
        
        $this->jenis_sampel_stats = $pendaftarQuery->with('jenisSampel')
            ->get()
            ->groupBy('jenisSampel.nama_sampel')
            ->map->count()
            ->toArray();

        // Parameter stats (agak berat jika banyak data, kita ambil top 10 parameter)
        // Parameter disimpan sebagai JSON ID di beberapa kasus atau relasi. 
        // Asumsi: kita pakai relasi results atau logic sederhana. 
        // Untuk performa, kita skip deep parameter filtering kecuali diminta detail. 
        // User minta "pendaftaran(jumlah,jenis sampel,parameter)"
        // Kita hitung parameter frekuensi dari HasilLingkungan yang terkait pendaftar range ini?
        // Atau parse kolom 'parameter' (json).
        
        // Pendekatan Query Parameter (JSON Parsing MySQL 5.7+ support or fetching all)
        // Kita ambil semua pendaftar di range ini, loop parameter attributes.
        $allParams = [];
        $pendaftarQuery->chunk(100, function($pendaftars) use (&$allParams) {
            foreach($pendaftars as $p) {
                if ($p->mode_parameter) { // Manual
                     $params = $p->parameter ?? []; // array cast check model
                } else { // Paket
                     $params = $p->paket?->parameter ?? []; // paket parameter IDs
                }
                
                // Ensure array
                if (is_string($params)) $params = json_decode($params, true) ?? [];
                
                foreach($params as $pid) {
                    $allParams[$pid] = ($allParams[$pid] ?? 0) + 1;
                }
            }
        });
        // Sort and get names
        arsort($allParams);
        $topParams = array_slice($allParams, 0, 10, true);
        $paramNames = \App\Models\ParameterLingkungan::whereIn('id', array_keys($topParams))->pluck('nama_parameter', 'id');
        
        $this->parameter_stats = [];
        foreach($topParams as $pid => $count) {
            if(isset($paramNames[$pid])) {
                $this->parameter_stats[$paramNames[$pid]] = $count;
            }
        }


        // 2. Ekspedisi
        // Selisih waktu dari pendaftaran -> verifikasi hasil?
        // Ekspedisi linked to Pendaftar (no_pendaftar).
        // Kita ambil Ekspedisi yang TERKAIT pendaftar di periode ini, ATAU Ekspedisi yang created_at di periode ini?
        // Usually reports are based on Registration Date or Activity Date. Let's use Registration Date as base.
        $ekspedisiQuery = Ekspedisi::query()
            ->whereHas('pendaftarLingkungan', function($q) use ($start, $end) {
                $q->whereDate('tanggal_pendaftar', '>=', $start)
                  ->whereDate('tanggal_pendaftar', '<=', $end);
            });

        $ekspedisiData = $ekspedisiQuery->with('pendaftarLingkungan')->get();

        $this->ekspedisi_stats = [
            'Diterima' => $ekspedisiData->where('sampel_diterima', true)->count(),
            'Verifikasi' => $ekspedisiData->where('verifikasi_hasil', true)->count(),
            'Selesai' => $ekspedisiData->where('validasi2', true)->count(), // Validasi 2 assumes Selesai
            'Dimusnahkan' => $ekspedisiData->where('sampel_dimusnahkan', true)->count(),
        ];

        // Calc Avg Time (Pendaftar Created -> Verif Hasil Updated?)
        // Verif hasil is boolean toggle. We don't have timestamp for verification toggle unless audits enabled.
        // We have `tanggal_diterima` (datetime).
        // User asked "Selisih waktu dari pendaftaran hingga verif hasil". 
        // If we don't store Verif Date, we can't calc accurately. 
        // We do store `updated_at`. If verif is last step... 
        // Let's rely on `tanggal_diterima` - `tanggal_pendaftar` as "Response Time" for now or use `created_at` invoice?
        // User specific request: "Selisih waktu dari pendaftaran hingga verif hasil"
        // Without a specific "datetime_verifikasi" column, we can't do this 100%. 
        // I'll calculate `tanggal_diterima` - `created_at` (Pendaftaran) as a proxy for "TAT Penerimaan".
        
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


        // 3. Transaksi
        $invQuery = InvoiceLingkungan::query()
            ->whereDate('tanggal_tagihan', '>=', $start)
            ->whereDate('tanggal_tagihan', '<=', $end);
            
        $this->total_revenue = $invQuery->sum('total_bayar'); // Yang sudah dibayar real
        $this->total_paid = $invQuery->where('status_bayar', 2)->count(); // Lunas
        $this->total_unpaid = $invQuery->where('status_bayar', '!=', 2)->count(); // Belum Lunas
        
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
