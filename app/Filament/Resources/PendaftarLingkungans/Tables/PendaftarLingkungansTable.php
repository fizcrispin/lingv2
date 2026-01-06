<?php

namespace App\Filament\Resources\PendaftarLingkungans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Table;
use App\Filament\Resources\PendaftarLingkunganResource\Pages;
use App\Filament\Resources\PendaftarLingkunganResource\RelationManagers;
use App\Models\PendaftarLingkungan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextInputColumn;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Group;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use App\Models\ParameterLingkungan;
use Filament\Forms\Components\Toggle;
use App\Models\PaketParameter;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Textarea;
use Filament\Actions\CreateAction;
use Filament\Actions\ActionGroup;



class PendaftarLingkungansTable
{
    public static function configure(Table $table): Table
    {
   return $table
        ->reorderableColumns()
        ->deferColumnManager(false)
        ->defaultPaginationPageOption(10)
        ->paginated([10, 25, 75, 150, 300])
        ->columns([
            TextColumn::make('no_pendaftar')
                ->extraAttributes([
                    'class' => 'px-1 py-0 text-xs leading-tight',
                ])
                ->label('No')
                ->searchable()
                ->sortable()
                ->width('120px')
                ->weight('bold'),
                    
            TextColumn::make('tanggal_pendaftar')
                ->label('Tgl. Daftar')
                ->date('d/m/Y')
                ->sortable()
                ->extraAttributes([
                    'class' => 'px-1 py-0 text-xs leading-tight',
                ]),

            TextColumn::make('nama_pengirim')
                ->label('Nama Pengirim')
                ->limit(20)
                ->tooltip(fn (\App\Models\PendaftarLingkungan $record) => $record->nama_pengirim)
                ->width('120px')
                ->searchable()
                ->extraAttributes([
                    'class' => 'px-1 py-0 text-xs leading-tight',
                ]),

            TextColumn::make('jenisSampel.nama_sampel')
                ->label('Jenis Sampel')
                ->searchable()
                ->extraAttributes([
                    'class' => 'px-1 py-0 text-xs leading-tight',
                ]),

            TextInputColumn::make('titik_sampling')
                ->label('Titik Sampling')
                ->searchable()
                ->extraAttributes([
                    'class' => 'px-1 py-0 text-xs leading-tight',
                ])
                ->tooltip(fn ($record) => "⚠️ {$record->titik_sampling}"),

            TextColumn::make('parameter_list')
                ->label('Parameter')
                ->toggleable()
                ->limit(20)
                ->extraAttributes([
                    'class' => 'px-1 py-0 text-xs leading-tight',
                ])
                ->tooltip(function (\App\Models\PendaftarLingkungan $record) {
                    $allIds = [];
                    // 1. Ambil dari kolom parameter (manual)
                    $manualIds = $record->parameter; 
                    if (is_array($manualIds)) $allIds = array_merge($allIds, $manualIds);

                    // 2. Ambil dari paket_id
                    if (!empty($record->paket_id) && $record->paket && is_array($record->paket->parameter)) {
                        $allIds = array_merge($allIds, $record->paket->parameter);
                    }
                    
                    if (empty($allIds)) return '-';
                    $names = \App\Models\ParameterLingkungan::whereIn('id', array_unique($allIds))->pluck('nama_parameter')->toArray();
                    return implode(', ', $names);
                })
                ->formatStateUsing(function (\App\Models\PendaftarLingkungan $record) {
                    $allIds = [];
                    // 1. Ambil dari kolom parameter (manual)
                    $manualIds = $record->parameter; 
                    if (is_array($manualIds)) $allIds = array_merge($allIds, $manualIds);

                    // 2. Ambil dari paket_id
                    if (!empty($record->paket_id) && $record->paket && is_array($record->paket->parameter)) {
                        $allIds = array_merge($allIds, $record->paket->parameter);
                    }

                    $allIds = array_unique(array_filter(array_map('intval', $allIds)));
                    if (empty($allIds)) return '-';

                    $names = \App\Models\ParameterLingkungan::whereIn('id', $allIds)
                        ->pluck('nama_parameter')
                        ->toArray();
                    
                    return empty($names) ? '-' : implode(', ', $names);
                }),
                        TextColumn::make('total_harga')
                            ->toggleable()
                            ->label('Total Biaya')
                            ->money('IDR')
                            ->width('150px')
                            ->alignment('right')
                            ->weight('bold'),
                    ])

                        ->modifyQueryUsing(function ($query) {
                            $query->whereRaw("no_pendaftar REGEXP '^[0-9]+$'") // filter hanya angka
                                ->orderByRaw('CAST(no_pendaftar AS UNSIGNED) DESC')
                                ->orderByDesc('created_at');
                        })
                        ->filters([
                            //
                        ])
                        ->actions([
                            ActionGroup::make([
                                Action::make('cetak_label')
                                    ->label('Cetak Label')
                                    ->icon('heroicon-o-tag')
                                    ->color('info')
                                    ->url(fn ($record) => route('print.label', $record->id))
                                    ->openUrlInNewTab(),

                                EditAction::make()
                                    ->modalWidth('5xl')
                                    ->label('Edit Data'),

                                Action::make('proses_data')
                                    ->label('Proses Data')
                                    ->icon('heroicon-o-cog')
                                    ->color('success')
                                    ->requiresConfirmation()
                                    ->modalHeading('Proses Data Pendaftaran?')
                                    ->modalDescription('Aksi ini akan membuat/memperbarui data Ekspedisi, Invoice, dan Hasil Lingkungan berdasarkan data pendaftaran ini.')
                                    ->modalSubmitActionLabel('Ya, Proses')
                                    ->action(function ($record) {
                                        $record->processData();
                                        
                                        \Filament\Notifications\Notification::make()
                                            ->title('Data Berhasil Diproses')
                                            ->body('Ekspedisi, Invoice, dan Hasil Lingkungan telah dibuat/diperbarui.')
                                            ->success()
                                            ->send();
                                    }),

                                DeleteAction::make()
                                    ->label('Hapus'),

                                // ✅ Custom Duplicate Action untuk Filament v4
                                CreateAction::make('duplicate')
                                    ->label('Duplikasi')
                                    ->icon('heroicon-o-document-duplicate')
                                    ->color('success')
                                    ->form([
                                        Forms\Components\TextInput::make('jumlah')
                                            ->label('Berapa kali ingin menyalin data ini?')
                                            ->numeric()
                                            ->default(1)
                                            ->minValue(1)
                                            ->maxValue(50)
                                            ->required()
                                            ->hint('Maksimal 50 kali')
                                            ->live()
                                            ->suffix('kali'),
                                    ])
                                    ->action(function ($record, array $data): void {
                                        $jumlah = min((int) $data['jumlah'], 50);
                                        $total = 0;

                                        for ($i = 1; $i <= $jumlah; $i++) {
                                            $new = $record->replicate();
                                            
                                            // Ambil daftar nomor pendaftar terbaru setiap iterasi agar tidak bentrok
                                            $numbers = \App\Models\PendaftarLingkungan::pluck('no_pendaftar')
                                                ->map(fn($n) => (int) preg_replace('/\D/', '', $n))
                                                ->filter()
                                                ->sort()
                                                ->values();

                                            $expected = 1;
                                            foreach ($numbers as $n) {
                                                if ($n > $expected) break;
                                                $expected++;
                                            }

                                            // Atur nomor pendaftar baru yang urut
                                            $new->no_pendaftar = (string) $expected;

                                            // Tambahkan kembali tanda copy pada titik_sampling
                                            if (isset($new->titik_sampling)) {
                                                $new->titik_sampling = "copy{$i}-" . $record->titik_sampling;
                                            }
                                            
                                            $new->save();
                                            $total++;
                                        }

                                        Notification::make()
                                            ->title('Berhasil Menduplikasi!')
                                            ->body("Berhasil menduplikasi {$total} data baru.")
                                            ->success()
                                            ->send();
                                    })
                                    ->modalHeading('Duplikasi Data')
                                    ->modalDescription('Masukkan jumlah data yang ingin diduplikasi')
                                    ->modalSubmitActionLabel('Proses Duplikasi')
                                    ->modalIcon('heroicon-o-document-duplicate')
                                    ->modalWidth('md')
                                    ->requiresConfirmation(false), 
                            ])
                            ->icon('heroicon-m-bars-3')
                            ->tooltip('Menu Aksi'),
                        ])
                        ->actionsPosition(\Filament\Tables\Enums\RecordActionsPosition::BeforeColumns)
                        ->toolbarActions([
                            BulkActionGroup::make([
                                DeleteBulkAction::make(),
                            ]),
                        ]);
                }
            }
