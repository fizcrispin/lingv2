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
                //
            ])
            ->actionsPosition(\Filament\Tables\Enums\RecordActionsPosition::BeforeColumns)
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Input Hasil')
                    ->icon('heroicon-o-pencil-square')
                    ->modalWidth('7xl')
                    ->mutateRecordDataUsing(function (PendaftarLingkungan $record, array $data): array {
                        // Load latest data for form population
                        // Note: Sync logic moved to form() to ensure schema availability
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
                        
                        // --- SYNC LOGIC (Moved here to ensure Schema exists) ---
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
                            // Refresh Relationship
                            $record->load(['hasilLingkungans.parameter.kategoriData']);
                        }
                        // -------------------------------------------------------

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
                \Filament\Actions\Action::make('cetak')
                    ->iconButton()
                    ->tooltip('Cetak Hasil')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->url(fn (PendaftarLingkungan $record) => route('cetak.hasil', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                \Filament\Actions\BulkAction::make('cetak_masal')
                    ->label('Cetak Masal')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->action(function (\Illuminate\Database\Eloquent\Collection $records, \Livewire\Component $livewire) {
                        $url = route('cetak.hasil.bulk', ['ids' => implode(',', $records->pluck('id')->toArray())]);
                        $livewire->js("window.open('$url', '_blank')");
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInputHasils::route('/'),
        ];
    }
}
