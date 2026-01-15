<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InputHasilResource\Pages;
use App\Models\PendaftarLingkungan;
use BackedEnum;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;

class InputHasilResource extends Resource
{   
    protected static ?string $model = PendaftarLingkungan::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Operasional';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Hasil Laboratorium';
    
    protected static ?string $slug = 'hasil-lab';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                 Forms\Components\TextInput::make('no_pendaftar')->disabled(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('hasilLingkungans');
    }

    public static function table(Table $table): Table
    {
        return $table
        ->reorderableColumns()
        ->deferColumnManager(true)
        ->columns([
                Tables\Columns\TextColumn::make('no_pendaftar')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->toggleable()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tgl. Daftar')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('nama_pengirim')
                    ->label('Nama Pengirim')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->nama_pengirim),
                Tables\Columns\TextColumn::make('jenisSampel.nama_sampel')
                    ->label('Jenis Sampel')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('titik_sampling')
                    ->label('Titik Sampling')
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->titik_sampling)
                    ->placeholder('-'),
            ])
            ->modifyQueryUsing(function ($query) {
                $query->whereRaw("no_pendaftar REGEXP '^[0-9]+$'")
                    ->orderByRaw('CAST(no_pendaftar AS UNSIGNED) DESC')
                    ->orderByDesc('created_at');
            })
            ->filters([
                \Filament\Tables\Filters\Filter::make('tanggal_pendaftar')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        \Filament\Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_pendaftar', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_pendaftar', '<=', $date),
                            );
                    }),
                \Filament\Tables\Filters\SelectFilter::make('jenis_sampling')
                    ->label('Jenis Sampel')
                    ->relationship('jenisSampel', 'nama_sampel')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\Filter::make('belum_dicetak')
                    ->label('Belum Dicetak')
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave('statusData', fn ($q) => $q->where('dicetak', true))),
                \Filament\Tables\Filters\Filter::make('belum_notif')
                    ->label('Belum Notif')
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave('statusData', fn ($q) => $q->where('notifikasi', '1'))),
                \Filament\Tables\Filters\Filter::make('belum_diambil')
                    ->label('Belum Diambil')
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave('statusData', fn ($q) => $q->where('diambil', true))),
            ])
            ->actionsPosition(\Filament\Tables\Enums\RecordActionsPosition::BeforeColumns)
            ->actions([
                \Filament\Actions\ActionGroup::make([
                    EditAction::make()
                        ->label('Input Hasil')
                        ->icon('heroicon-o-pencil-square')
                        ->modalWidth('7xl')
                        ->mutateRecordDataUsing(function (PendaftarLingkungan $record, array $data): array {
                            // Load latest data for form population
                            $record->load(['hasilLingkungans.parameter.kategoriData', 'jenisSampel']);
                            
                            $hasilData = [];
                            foreach ($record->hasilLingkungans as $hasil) {
                                $hasilData[$hasil->id] = [
                                    'hasil_parameter' => $hasil->hasil_parameter,
                                    'tanggal_input' => $hasil->tanggal_input,
                                    'keterangan' => $hasil->keterangan,
                                ];
                            }
                            $data['hasil_data'] = $hasilData;
                            
                            return $data;
                        })
                        ->form(function (?PendaftarLingkungan $record) {
                            if (!$record) return [];
                            
                            if ($record->hasilLingkungans->isEmpty()) {
                                $params = $record->all_parameters;
                                foreach ($params as $param) {
                                    \App\Models\HasilLingkungan::firstOrCreate([
                                        'id_pendaftar' => $record->id,
                                        'id_parameter' => $param->id,
                                    ], [
                                        'nama_parameter' => $param->nama_parameter,
                                        'tanggal_input' => now(), 
                                    ]);
                                }
                                $record->load(['hasilLingkungans.parameter.kategoriData']);
                            }
                            // Form schema identical to previous
                            // To avoid repeating large blocks of code, I will reference the logic above task
                            // But since replace_file_content requires exact replacement, I must include the FULL Form definition if I'm replacing the Action definition.
                            // The user has this file locally. I can try to surgically replace just the wrapper if possible, but the indentation changes.
                            // To be safe and since I can't partially match easily with indentation, I will reproduce the `form` content.
                            // However, the `form` logic is huge (lines 142-334).
                            // Strategy: I will keep the original `form` definition closure as is, just wrapped.
                            // Wait, the prompt instruction allows me to provide `ReplacementContent`.
                            // I should re-use the exact same code for the form closure.
                            
                            // Let's copy the form closure content from the file view.
                             $schema = [];

                            // SECTION 1: Informasi Pendaftaran - Stylist & Compact
                            $schema[] = \Filament\Schemas\Components\Section::make('Informasi Pendaftaran')
                                ->icon('heroicon-o-information-circle')
                                ->schema([
                                    Forms\Components\Placeholder::make('no_pendaftar_display')
                                        ->label('No. Pendaftaran')
                                        ->content($record->no_pendaftar)
                                        ->inlineLabel()
                                        ->extraAttributes(['style' => 'border-bottom: 1px solid #f1f5f9; padding-bottom: 1px; border-left: 3px solid #3b82f6; padding-left: 8px; font-weight: 700; color: #1d4ed8;']),
                                    
                                    Forms\Components\Placeholder::make('tgl_pendaftar_display')
                                        ->label('Tanggal Daftar')
                                        ->content($record->tanggal_pendaftar ? \Carbon\Carbon::parse($record->tanggal_pendaftar)->translatedFormat('d F Y') : '-')
                                        ->inlineLabel()
                                        ->extraAttributes(['style' => 'border-bottom: 1px solid #f1f5f9; padding-bottom: 1px; border-left: 3px solid #8b5cf6; padding-left: 8px;']),
                                    
                                    Forms\Components\Placeholder::make('nama_pengirim_display')
                                        ->label('Nama Pengirim')
                                        ->content($record->nama_pengirim)
                                        ->inlineLabel()
                                        ->extraAttributes(['style' => 'border-bottom: 1px solid #f1f5f9; padding-bottom: 1px; border-left: 3px solid #8b5cf6; padding-left: 8px;']),

                                    Forms\Components\Placeholder::make('no_hp_display')
                                        ->label('No. HP / Telp')
                                        ->content($record->no_hp ?? '-')
                                        ->inlineLabel()
                                        ->extraAttributes(['style' => 'border-bottom: 1px solid #f1f5f9; padding-bottom: 1px; border-left: 3px solid #8b5cf6; padding-left: 8px;']),

                                    Forms\Components\Placeholder::make('jenis_sampel_display')
                                        ->label('Jenis Sampel')
                                        ->content($record->jenisSampel?->nama_sampel ?? '-')
                                        ->inlineLabel()
                                        ->extraAttributes(['style' => 'border-bottom: 1px solid #f1f5f9; padding-bottom: 1px; border-left: 3px solid #8b5cf6; padding-left: 8px;']),

                                    Forms\Components\Placeholder::make('titik_sampling_display')
                                        ->label('Titik Sampling')
                                        ->content($record->titik_sampling ?? '-')
                                        ->inlineLabel()
                                        ->extraAttributes(['style' => 'border-bottom: 1px solid #f1f5f9; padding-bottom: 1px; border-left: 3px solid #8b5cf6; padding-left: 8px;']),

                                    Forms\Components\Placeholder::make('tgl_sampling_display')
                                        ->label('Tanggal Sampling')
                                        ->content($record->tanggal_sampling ? \Carbon\Carbon::parse($record->tanggal_sampling)->format('d F Y') : '-')
                                        ->inlineLabel()
                                        ->extraAttributes(['style' => 'border-bottom: 1px solid #f1f5f9; padding-bottom: 1px; border-left: 3px solid #8b5cf6; padding-left: 8px;']),

                                    Forms\Components\Placeholder::make('petugas_display')
                                        ->label('Petugas Sampling')
                                        ->content($record->petugas_sampling ?? '-')
                                        ->inlineLabel()
                                        ->extraAttributes(['style' => 'border-bottom: 1px solid #f1f5f9; padding-bottom: 1px; border-left: 3px solid #8b5cf6; padding-left: 8px;']),

                                    Forms\Components\Placeholder::make('alamat_display')
                                        ->label('Alamat Sampling')
                                        ->content($record->alamat_sampling ?? '-')
                                        ->inlineLabel()
                                        ->extraAttributes(['style' => 'border-bottom: 1px solid #f1f5f9; padding-bottom: 1px; border-left: 3px solid #8b5cf6; padding-left: 8px;']),

                                    Forms\Components\Placeholder::make('keterangan_display')
                                        ->label('Keterangan')
                                        ->content($record->keterangan ?? '-')
                                        ->inlineLabel()
                                        ->extraAttributes(['style' => 'border-bottom: 1px solid #f1f5f9; padding-bottom: 1px; border-left: 3px solid #8b5cf6; padding-left: 8px;']),
                                ])
                                ->columns(2)
                                ->compact();

                            // SECTION 2: Dynamic Categories with Modern Table Design
                            $results = $record->hasilLingkungans->sortBy('nama_parameter');
                            $grouped = $results->groupBy(function ($item) {
                                return strtoupper($item->parameter?->kategoriData?->nama_kategori ?? 'Tanpa Kategori');
                            });

                            // Force Order: BAKTERIOLOGI, FISIKA, KIMIA
                            $order = ['BAKTERIOLOGI', 'FISIKA', 'KIMIA'];
                            $grouped = $grouped->sortBy(function ($items, $key) use ($order) {
                                $pos = array_search($key, $order);
                                return $pos === false ? 999 : $pos;
                            });

                            $categoryIndex = 0;
                            foreach ($grouped as $kategori => $items) {
                                $fields = [];
                                $categoryIndex++;

                                // Modern Category Header - Compact
                                $fields[] = \Filament\Forms\Components\Placeholder::make("header_{$kategori}")
                                    ->hiddenLabel()
                                    ->content(new \Illuminate\Support\HtmlString("
                                        <div style='display: flex; align-items: center; gap: 10px; padding: 10px 16px; background: linear-gradient(135deg, #1e40af 0%, #7c3aed 100%); border-radius: 8px; margin-bottom: 0;'>
                                            <div style='width: 28px; height: 28px; background: rgba(255,255,255,0.2); border-radius: 6px; display: flex; align-items: center; justify-content: center;'>
                                                <span style='color: white; font-weight: 700; font-size: 14px;'>{$categoryIndex}</span>
                                            </div>
                                            <div>
                                                <h4 style='margin: 0; color: white; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;'>{$kategori}</h4>
                                            </div>
                                        </div>
                                    "))
                                    ->columnSpanFull();

                                // Table Header - Ultra Compact
                                $fields[] = \Filament\Schemas\Components\Group::make([
                                    Forms\Components\Placeholder::make("th_param_{$kategori}")->hiddenLabel()->content(new \Illuminate\Support\HtmlString("<span style='font-size: 12px; font-weight: 700; color: #000000ff; text-transform: uppercase;'>Parameter</span>"))->columnSpan(4),
                                    Forms\Components\Placeholder::make("th_hasil_{$kategori}")->hiddenLabel()->content(new \Illuminate\Support\HtmlString("<span style='font-size: 12px; font-weight: 700; color: #0051ffff; text-transform: uppercase; display: block; text-align: center;'>Hasil</span>"))->columnSpan(2),
                                    Forms\Components\Placeholder::make("th_batas_{$kategori}")->hiddenLabel()->content(new \Illuminate\Support\HtmlString("<span style='font-size: 12px; font-weight: 700; color: #000000ff; text-transform: uppercase;'>Batas</span>"))->columnSpan(1),
                                    Forms\Components\Placeholder::make("th_satuan_{$kategori}")->hiddenLabel()->content(new \Illuminate\Support\HtmlString("<span style='font-size: 12px; font-weight: 700; color: #000000ff; text-transform: uppercase;'>Sat.</span>"))->columnSpan(1),
                                    Forms\Components\Placeholder::make("th_metode_{$kategori}")->hiddenLabel()->content(new \Illuminate\Support\HtmlString("<span style='font-size: 12px; font-weight: 700; color: #000000ff; text-transform: uppercase;'>Metode</span>"))->columnSpan(1),
                                    Forms\Components\Placeholder::make("th_tgl_{$kategori}")->hiddenLabel()->content(new \Illuminate\Support\HtmlString("<span style='font-size: 12px; font-weight: 700; color: #000000ff; text-transform: uppercase;'>Tgl</span>"))->columnSpan(2),
                                    Forms\Components\Placeholder::make("th_ket_{$kategori}")->hiddenLabel()->content(new \Illuminate\Support\HtmlString("<span style='font-size: 12px; font-weight: 700; color: #000000ff; text-transform: uppercase;'>Ket.</span>"))->columnSpan(1),
                                ])->columns(12)
                                  ->extraAttributes(['style' => 'background: #f8fafc; padding: 4px 12px; border-bottom: 2px solid #e5e7eb;']);

                                // Data Rows - Ultra Compact
                                $rowIndex = 0;
                                foreach ($items as $item) {
                                    $p = $item->parameter;
                                    $rowIndex++;
                                    $bgColor = $rowIndex % 2 === 0 ? '#ffffff' : '#f9fafb';

                                    $fields[] = \Filament\Schemas\Components\Group::make([
                                        Forms\Components\Placeholder::make("info_name_{$item->id}")
                                            ->hiddenLabel()
                                            ->content(new \Illuminate\Support\HtmlString("<div style='display: flex; align-items: center; gap: 6px;'><span style='min-width: 20px; height: 20px; background: #e0e7ff; color: #4338ca; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 600;'>{$rowIndex}</span><span style='font-weight: 500; font-size: 12px; color: #000000ff;'>{$item->nama_parameter}</span></div>"))
                                            ->columnSpan(4),
                                        Forms\Components\TextInput::make("hasil_data.{$item->id}.hasil_parameter")
                                            ->hiddenLabel()
                                            ->placeholder('—')
                                            ->columnSpan(2)
                                            ->extraInputAttributes(['style' => 'text-align: center; font-weight: 700; color: #1d4ed8; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; padding: 2px 4px; height: 28px; font-size: 12px;']),
                                        Forms\Components\Placeholder::make("info_batas_{$item->id}")
                                            ->hiddenLabel()
                                            ->content(new \Illuminate\Support\HtmlString("<span style='display: inline-block; background: #fef2f2; color: #dc2626; font-size: 11px; padding: 2px 6px; border-radius: 4px; font-weight: 600;'>".($p->batas_max ?? '—')."</span>"))
                                            ->columnSpan(1),
                                        Forms\Components\Placeholder::make("info_satuan_{$item->id}")
                                            ->hiddenLabel()
                                            ->content(new \Illuminate\Support\HtmlString("<span style='font-size: 11px; color: #000000ff;'>".($p->satuan ?? '—')."</span>"))
                                            ->columnSpan(1),
                                        Forms\Components\Placeholder::make("info_metode_{$item->id}")
                                            ->hiddenLabel()
                                            ->content(new \Illuminate\Support\HtmlString("<span style='font-size: 10px; color: #000000ff; font-style: italic;'>".($p->metode_pemeriksaan ?? '—')."</span>"))
                                            ->columnSpan(1),
                                        Forms\Components\DatePicker::make("hasil_data.{$item->id}.tanggal_input")
                                            ->hiddenLabel()
                                            ->columnSpan(2)
                                            ->extraInputAttributes(['style' => 'font-size: 11px; border-radius: 4px; padding: 2px 4px; height: 28px;']),
                                        Forms\Components\TextInput::make("hasil_data.{$item->id}.keterangan")
                                            ->hiddenLabel()
                                            ->placeholder('........')
                                            ->columnSpan(1)
                                            ->extraInputAttributes(['style' => 'font-size: 11px; border-radius: 4px; padding: 2px 4px; height: 28px;']),
                                    ])->columns(12)
                                      ->extraAttributes(['style' => "background: {$bgColor}; padding: 4px 12px; border-bottom: 1px solid #f3f4f6; transition: background 0.15s;"]); 
                                }

                                // Category Card Container
                                $schema[] = \Filament\Schemas\Components\Section::make()
                                    ->schema($fields)
                                    ->columnSpanFull()
                                    ->extraAttributes(['style' => 'background: white; border-radius: 16px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.07); overflow: hidden; margin-bottom: 20px;']);
                            }

                            return $schema;
                        })
                        ->using(function (PendaftarLingkungan $record, array $data): PendaftarLingkungan {
                            if (isset($data['hasil_data'])) {
                                foreach ($data['hasil_data'] as $id => $values) {
                                    \App\Models\HasilLingkungan::where('id', $id)->update($values);
                                }
                            }
                            return $record;
                        }),

                    \Filament\Actions\Action::make('status')
                        ->icon('heroicon-o-flag')
                        ->color('warning')
                        ->label('Update Status')
                        ->tooltip('Update Status')
                        ->modalHeading('Update Status Dokumen')
                        ->form([
                            \Filament\Schemas\Components\Section::make('Notifikasi Pelanggan')
                                ->schema([
                                    \Filament\Schemas\Components\Actions::make([
                                        \Filament\Actions\Action::make('kirim_wa')
                                            ->label('Kirim Notifikasi WA')
                                            ->icon('heroicon-o-chat-bubble-left-right')
                                            ->color('success')
                                            ->action(function ($record, \Livewire\Component $livewire) {
                                                // 1. Update DB
                                                $record->statusData()->updateOrCreate(
                                                    ['id_pendaftar' => $record->id],
                                                    ['notifikasi' => '1']
                                                );

                                                // 2. Prepare WA URL
                                                $phone = $record->no_hp;
                                                if (empty($phone)) {
                                                    \Filament\Notifications\Notification::make()
                                                        ->title('Nomor HP tidak tersedia')
                                                        ->danger()
                                                        ->send();
                                                    return;
                                                }
                                                // Format Phone
                                                if (substr(trim($phone), 0, 1) === '0') {
                                                    $phone = '62' . substr(trim($phone), 1);
                                                }

                                                // Data for Template
                                                $name = $record->nama_pengirim;
                                                $jenisSampel = $record->jenisSampel->nama_sampel ?? '-';
                                                $titikSampling = $record->titik_sampling ?? '-';

                                                $message = "===========================\n" .
                                                           "Yth Bapak/Ibu {$name}\n\n" .
                                                           "Kami dari Laboratorium Kesehatan Kab. Sragen, menyampaikan bahwa pemeriksaan lab anda telah selesai.\n\n" .
                                                           "Jenis Sampel : {$jenisSampel}\n" .
                                                           "Titik Sampling : {$titikSampling}\n\n" .
                                                           "Lembar hasil pemeriksaan dapat segera diambil dengan menunjukan bukti pembayaran.\n\n" .
                                                           "Atas Perhatiannya kami ucapkan terima kasih.\n" .
                                                           "===========================";

                                                $url = "https://wa.me/{$phone}?text=" . urlencode($message);

                                                $livewire->js("window.open('$url', '_blank')");
                                            })
                                            ,
                                        \Filament\Actions\Action::make('reset_status')
                                            ->label('Reset Status')
                                            ->icon('heroicon-o-arrow-path')
                                            ->color('danger')
                                            ->requiresConfirmation()
                                            ->action(function ($record) {
                                                $record->statusData()->updateOrCreate(
                                                    ['id_pendaftar' => $record->id],
                                                    ['notifikasi' => '0']
                                                );
                                                \Filament\Notifications\Notification::make()
                                                    ->title('Status Notifikasi Direset')
                                                    ->success()
                                                    ->send();
                                            })
                                    ])
                                    ->fullWidth(),
                                    \Filament\Forms\Components\Placeholder::make('info_notif')
                                        ->label('Status Notifikasi')
                                        ->content(fn ($record) => $record->statusData?->notifikasi == '1' ? 'Sudah Dikirim (1)' : 'Belum Dikirim')
                                        ->inlineLabel(),
                                ]),

                            \Filament\Schemas\Components\Grid::make(1)->schema([
                                \Filament\Forms\Components\Toggle::make('dicetak')
                                    ->label('Sudah Dicetak?')
                                    ->inline(false),

                                \Filament\Schemas\Components\Group::make([
                                    \Filament\Forms\Components\Toggle::make('diambil')
                                        ->label('Sudah Diambil?')
                                        ->live()
                                        ->inline(false),

                                    \Filament\Forms\Components\TextInput::make('pengambil')
                                        ->label('Nama Pengambil')
                                        ->visible(fn ($get) => $get('diambil')),
                                    
                                    \Filament\Forms\Components\DateTimePicker::make('tanggal_diambil')
                                        ->label('Tanggal Pengambilan')
                                        ->visible(fn ($get) => $get('diambil')),
                                ])->columns(3),
                                
                                \Filament\Forms\Components\Textarea::make('keterangan')
                                    ->label('Keterangan Tambahan')
                                    ->rows(3),
                            ])
                        ])
                        ->fillForm(fn (PendaftarLingkungan $record): array => [
                            'dicetak' => $record->statusData?->dicetak ?? false,
                            'diambil' => $record->statusData?->diambil ?? false,
                            'pengambil' => $record->statusData?->pengambil,
                            'tanggal_diambil' => $record->statusData?->tanggal_diambil,
                            'keterangan' => $record->statusData?->keterangan,
                        ])
                        ->action(function (PendaftarLingkungan $record, array $data): void {
                            $record->statusData()->updateOrCreate(
                                ['id_pendaftar' => $record->id],
                                [
                                    'dicetak' => $data['dicetak'] ?? false,
                                    'diambil' => $data['diambil'] ?? false,
                                    'pengambil' => $data['pengambil'] ?? null,
                                    'tanggal_diambil' => $data['tanggal_diambil'] ?? null,
                                    'keterangan' => $data['keterangan'] ?? null,
                                ]
                            );
                            \Filament\Notifications\Notification::make()
                                ->title('Status Berhasil Disimpan')
                                ->success()
                                ->send();
                        }),

                    \Filament\Actions\Action::make('cetak')
                        ->label('Cetak Hasil')
                        ->icon('heroicon-o-printer')
                        ->color('success')
                        ->visible(fn (PendaftarLingkungan $record) => 
                            $record->ekspedisi?->verifikasi_hasil && 
                            $record->ekspedisi?->validasi1 && 
                            $record->ekspedisi?->validasi2
                        )
                        ->url(fn (PendaftarLingkungan $record) => route('cetak.hasil', $record))
                        ->openUrlInNewTab(),
                ])
                ->icon('heroicon-m-bars-3')
                ->tooltip('Menu Aksi'),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                \Filament\Actions\BulkAction::make('cetak_masal')
                    ->label('Cetak Masal')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records, \Livewire\Component $livewire) {
                        $url = route('cetak.hasil.bulk', ['ids' => implode(',', $records->pluck('id')->toArray())]);
                        $livewire->js("window.open('$url', '_blank')");
                    })
                    ->deselectRecordsAfterCompletion(),
                \Filament\Actions\BulkAction::make('atur_notifikasi_bulk')
                    ->label('Atur Notifikasi WA')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->form([
                         \Filament\Forms\Components\Radio::make('mode')
                            ->label('Pilih Tindakan')
                            ->options([
                                'kirim' => 'Kirim Notifikasi WA (Buka Tab Baru)',
                                'reset' => 'Reset Status Notifikasi (Jadi Belum Dikirim)',
                            ])
                            ->default('kirim')
                            ->required()
                            ->descriptions([
                                'kirim' => 'Akan membuka tab WhatsApp untuk setiap pelanggan terpilih.',
                                'reset' => 'Gunakan jika pesan gagal terkirim atau ingin mengirim ulang.',
                            ]),
                    ])
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data, \Livewire\Component $livewire) {
                        $mode = $data['mode'];

                        if ($mode === 'reset') {
                            // Logic Reset
                            foreach ($records as $record) {
                                $record->statusData()->updateOrCreate(
                                    ['id_pendaftar' => $record->id],
                                    ['notifikasi' => '0']
                                );
                            }
                            \Filament\Notifications\Notification::make()
                                ->title('Status Notifikasi Berhasil Direset')
                                ->success()
                                ->send();

                        } else {
                            // Logic Kirim WA
                            // 1. Group by Phone Number
                            $grouped = $records->groupBy(function ($item) {
                                return $item->no_hp;
                            });

                            $jsCommands = "";
                            $countSent = 0;
                            $countSkipped = 0;

                            foreach ($grouped as $phone => $items) {
                                if (empty($phone)) {
                                    $countSkipped += $items->count();
                                    continue;
                                }

                                // Clean Phone
                                $targetPhone = $phone;
                                if (substr(trim($targetPhone), 0, 1) === '0') {
                                    $targetPhone = '62' . substr(trim($targetPhone), 1);
                                }

                                // Mark Notified
                                foreach ($items as $item) {
                                    $item->statusData()->updateOrCreate(
                                        ['id_pendaftar' => $item->id],
                                        ['notifikasi' => '1']
                                    );
                                }

                                // Build Message
                                $firstItem = $items->first();
                                $namaPelanggan = $firstItem->nama_pengirim;
                                
                                $jenisSampelList = $items->map(fn($i) => $i->jenisSampel->nama_sampel ?? '-')->unique()->implode(', ');

                                $listTitik = "";
                                $counter = 1;
                                foreach ($items as $item) {
                                    $noReg = $item->no_pendaftar;
                                    $titik = $item->titik_sampling ?? '-';
                                    $listTitik .= "{$counter}. ({$noReg}) {$titik}\n";
                                    $counter++;
                                }

                                $message = "===========================\n" .
                                           "Yth Bapak/Ibu {$namaPelanggan}\n" .
                                           "Kami dari Laboratorium Kesehatan Kab. Sragen, menyampaikan bahwa pemeriksaan lab anda telah selesai.\n\n" .
                                           "Jenis Sampel : {$jenisSampelList}\n" .
                                           "Titik Sampling : \n" .
                                           $listTitik . "\n" .
                                           "Lembar hasil pemeriksaan dapat segera diambil dengan menunjukan bukti pembayaran.\n\n" .
                                           "Atas Perhatiannya kami ucapkan terima kasih.\n" .
                                           "===========================";

                                $url = "https://wa.me/{$targetPhone}?text=" . urlencode($message);
                                $jsCommands .= "window.open('$url', '_blank'); ";
                                $countSent++;
                            }

                            if ($countSent > 0) {
                                 $livewire->js($jsCommands);
                                 \Filament\Notifications\Notification::make()
                                    ->title("Proses Kirim WA Berjalan")
                                    ->body("Membuka {$countSent} tab WhatsApp.")
                                    ->success()
                                    ->send();
                            }

                            if ($countSkipped > 0) {
                                \Filament\Notifications\Notification::make()
                                    ->title("Data Diabaikan")
                                    ->body("{$countSkipped} data tanpa No HP.")
                                    ->warning()
                                    ->send();
                            }
                        }
                    })
                    ->deselectRecordsAfterCompletion(),
                    
                \Filament\Actions\BulkAction::make('bulk_status_edit')
                    ->label('Edit Status (Bulk)')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->form([
                        \Filament\Forms\Components\Select::make('notifikasi')
                            ->label('Status Notifikasi')
                            ->options([
                                '1' => 'Sudah Dikirim',
                                '0' => 'Belum Dikirim',
                            ])
                            ->placeholder('Tidak Berubah'),
                            
                        \Filament\Forms\Components\Select::make('dicetak')
                            ->label('Status Dicetak')
                            ->options([
                                1 => 'Sudah Dicetak',
                                0 => 'Belum Dicetak',
                            ])
                            ->placeholder('Tidak Berubah'),

                        \Filament\Forms\Components\Select::make('diambil')
                            ->label('Status Diambil')
                            ->options([
                                1 => 'Sudah Diambil',
                                0 => 'Belum Diambil',
                            ])
                            ->placeholder('Tidak Berubah')
                            ->live(),

                        \Filament\Forms\Components\TextInput::make('pengambil')
                            ->label('Nama Pengambil')
                            ->visible(fn ($get) => $get('diambil') == 1),
                        
                        \Filament\Forms\Components\DateTimePicker::make('tanggal_diambil')
                            ->label('Tanggal Pengambilan')
                            ->visible(fn ($get) => $get('diambil') == 1),
                    ])
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                       foreach ($records as $record) {
                           $updateData = [];
                           
                           if (isset($data['notifikasi'])) $updateData['notifikasi'] = $data['notifikasi'];
                           if (isset($data['dicetak'])) $updateData['dicetak'] = (bool)$data['dicetak'];
                           
                           if (isset($data['diambil'])) {
                               $updateData['diambil'] = (bool)$data['diambil'];
                               // If set to Taken, update taker info if provided
                               if ($data['diambil'] == 1) {
                                   if (!empty($data['pengambil'])) $updateData['pengambil'] = $data['pengambil'];
                                   if (!empty($data['tanggal_diambil'])) $updateData['tanggal_diambil'] = $data['tanggal_diambil'];
                               } 
                               // If set to Not Taken, maybe clear info? Or leave it history? Usually clear or ignore.
                           }

                           if (!empty($updateData)) {
                               $record->statusData()->updateOrCreate(
                                   ['id_pendaftar' => $record->id],
                                   $updateData
                               );
                           }
                       }
                       
                       \Filament\Notifications\Notification::make()
                           ->title('Status Berhasil Diperbarui')
                           ->success()
                           ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInputHasils::route('/'),
        ];
    }
}
