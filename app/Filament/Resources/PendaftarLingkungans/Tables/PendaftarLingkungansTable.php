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
        ->deferColumnManager(true)
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
                ->weight('bold')
                ->extraAttributes([
                    'style' => 'width: 50px;',
                ]),
                    
            TextColumn::make('tanggal_pendaftar')
                ->label('Tgl. Daftar')
                ->date('d/m/Y')
                ->sortable()
                ->extraAttributes([
                    'class' => 'px-1 py-0 text-xs leading-tight',
                    // 'style' => 'width: 50px;',
                ]),

            TextColumn::make('nama_pengirim')
                ->label('Nama Pengirim')
                ->limit(20)
                ->tooltip(fn (\App\Models\PendaftarLingkungan $record) => $record->nama_pengirim)
                // ->width('120px')
                ->searchable()
                ->weight(fn ($record) => $record->ekspedisi ? null : 'bold')
                ->color(fn ($record) => $record->ekspedisi ? null : 'warning')
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
                ->limit(30)
                ->tooltip(function (\App\Models\PendaftarLingkungan $record) {
                    $allIds = [];
                    // 1. Manual
                    if (!empty($record->parameter)) {
                        $p = is_array($record->parameter) ? $record->parameter : json_decode($record->parameter, true);
                        if (is_array($p)) $allIds = array_merge($allIds, $p);
                    }
                    // 2. Paket
                    if ($record->paket_id && $record->paket) {
                        $pp = $record->paket->parameter;
                        $pp = is_array($pp) ? $pp : json_decode($pp, true);
                        if (is_array($pp)) $allIds = array_merge($allIds, $pp);
                    }
                    
                    $allIds = array_filter(array_unique($allIds));
                    if (empty($allIds)) return '-';
                    
                    return \App\Models\ParameterLingkungan::whereIn('id', $allIds)
                        ->pluck('nama_parameter')
                        ->join(', ');
                })
                ->wrap()
                ->color('gray')
                ->extraAttributes([
                    'class' => 'px-1 py-0 text-xs leading-tight',
                    'style' => 'width: 100px;',
                ])
                ->state(function (\App\Models\PendaftarLingkungan $record) {
                    $allIds = [];
                    // 1. Manual
                    if (!empty($record->parameter)) {
                        $p = is_array($record->parameter) ? $record->parameter : json_decode($record->parameter, true);
                        if (is_array($p)) $allIds = array_merge($allIds, $p);
                    }
                    // 2. Paket
                    if ($record->paket_id && $record->paket) {
                        $pp = $record->paket->parameter;
                        $pp = is_array($pp) ? $pp : json_decode($pp, true);
                        if (is_array($pp)) $allIds = array_merge($allIds, $pp);
                    }
                    
                    $allIds = array_filter(array_unique($allIds));
                    if (empty($allIds)) return '-';
                    
                    return \App\Models\ParameterLingkungan::whereIn('id', $allIds)
                        ->pluck('nama_parameter')
                        ->join(', ');
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
                            $query->with(['ekspedisi'])
                                ->whereRaw("no_pendaftar REGEXP '^[0-9]+$'")
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
                                BulkAction::make('proses_data')
                                    ->label('Proses Data')
                                    ->icon('heroicon-o-cog')
                                    ->color('success')
                                    ->requiresConfirmation()
                                    ->modalHeading('Proses Data Terpilih?')
                                    ->modalDescription('Aksi ini akan memproses semua data pendaftaran yang dipilih.')
                                    ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                                        $count = 0;
                                        foreach ($records as $record) {
                                            $record->processData();
                                            $count++;
                                        }
                                        
                                        \Filament\Notifications\Notification::make()
                                            ->title('Bulk Proses Berhasil')
                                            ->body("{$count} data telah diproses.")
                                            ->success()
                                            ->send();
                                    }),
                                BulkAction::make('cetak_label')
                                    ->label('Cetak Label')
                                    ->icon('heroicon-o-tag')
                                    ->color('info')
                                    ->deselectRecordsAfterCompletion()
                                    ->action(function (\Illuminate\Database\Eloquent\Collection $records, \Livewire\Component $livewire) {
                                        $ids = $records->pluck('id')->implode(',');
                                        $url = route('print.label.bulk', ['ids' => $ids]);
                                        $livewire->js("window.open('{$url}', '_blank')");
                                    }),
                            ]),
                        ]);
                }
            }
