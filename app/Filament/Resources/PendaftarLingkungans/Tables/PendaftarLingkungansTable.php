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
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Actions\CreateAction;



class PendaftarLingkungansTable
{
    public static function configure(Table $table): Table
    {
   return $table
        ->defaultPaginationPageOption(10)
        ->paginated([10, 25, 75, 150, 300])
        ->columns([
            TextColumn::make('no_pendaftar')
                ->label('No. Pendaftar')
                ->searchable()
                ->sortable()
                ->width('120px')
                ->weight('bold'),


            TextColumn::make('tanggal_pendaftar')
                ->label('Tgl. Daftar')
                ->date('d/m/Y')
                ->sortable(),

            TextColumn::make('nama_pengirim')
                ->label('Nama Pengirim')
                ->width('120px')
                ->searchable(),

            TextColumn::make('jenisSampel.kode_sampel')
                ->label('Jenis Sampel')
                ->searchable(),

            TextInputColumn::make('titik_sampling')
                ->label('Titik Sampling')
                ->searchable()
                ->tooltip(fn ($record) => "⚠️ {$record->titik_sampling}"),

            TextColumn::make('parameter_list')
                ->label('Parameter Reguler')
                ->wrapHeader()
                ->wrap()
                ->width('350px')
                ->html()
                ->formatStateUsing(function (\App\Models\PendaftarLingkungan $record) {
                    $allIds = [];

                    // 1. Ambil dari kolom parameter (manual)
                    $manualIds = $record->parameter; 
                    if (is_array($manualIds)) {
                        $allIds = array_merge($allIds, $manualIds);
                    }

                    // 2. Ambil dari paket_id
                    if (!empty($record->paket_id)) {
                        $paket = $record->paket; 
                        if ($paket && is_array($paket->parameter)) {
                            $allIds = array_merge($allIds, $paket->parameter);
                        }
                    }

                    $allIds = array_unique(array_filter(array_map('intval', $allIds)));

                    if (empty($allIds)) {
                        return '-';
                    }

                    $names = \App\Models\ParameterLingkungan::whereIn('id', $allIds)
                        ->pluck('nama_parameter')
                        ->toArray();

                    if (empty($names)) {
                        return '-';
                    }

                    return new \Illuminate\Support\HtmlString('<div class="text-xs leading-relaxed">' . e(implode(', ', $names)) . '</div>');
                }),
                        TextColumn::make('total_harga')
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
                        ->recordActions([

                            EditAction::make()
                                ->modalWidth('7xl'),
                Action::make('buat_tagihan')
                    ->label('Buat Tagihan')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->hidden(fn ($record) => $record->invoice()->exists())
                    ->action(function ($record) {
                        $record->invoice()->create([
                            'tanggal_tagihan' => now(),
                            'total_harga' => $record->total_harga,
                            'status_bayar' => 1,
                            'total_bayar' => 0,
                            'kode_ver' => '',
                        ]);

                        Notification::make()
                            ->title('Tagihan Berhasil Dibuat')
                            ->success()
                            ->send();
                    }),

                            DeleteAction::make()
                            ->iconButton()
                            ->tooltip('Hapus Data Ini'),

                        // ✅ Custom Duplicate Action untuk Filament v4
                            CreateAction::make('duplicate')
                            ->icon('heroicon-o-document-duplicate')
                            ->iconButton()
                            ->tooltip('Duplikasi data ini')
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
                            ->requiresConfirmation(false), // Ubah ke true jika ingin konfirmasi tambahan

                        ])
                        ->toolbarActions([
                            BulkActionGroup::make([
                                DeleteBulkAction::make(),
                            ]),
                        ]);
                }
            }
