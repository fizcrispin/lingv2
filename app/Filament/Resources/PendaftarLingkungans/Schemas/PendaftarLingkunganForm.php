<?php

namespace App\Filament\Resources\PendaftarLingkungans\Schemas;

use Filament\Schemas\Schema;
use App\Filament\Resources\PendaftarLingkunganResource\Pages;
use App\Filament\Resources\PendaftarLingkunganResource\RelationManagers;
use App\Models\PendaftarLingkungan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\ReplicateAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextInputColumn;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Notifications\Notification;
use App\Models\ParameterLingkungan;
use Filament\Forms\Components\Toggle;
use App\Models\PaketParameter;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Grouping\Group;
use Filament\Actions\CreateAction;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Radio;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Forms\Components\TimePicker;




class PendaftarLingkunganForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
            // Status & Informasi Pendaftar
            Grid::make(['default' => 1, 'sm' => 2, 'lg' => 3])
                ->schema([

                            TextInput::make('no_pendaftar')
                                ->label('No. Pendaftar')
                                ->required()
                                ->maxLength(128)
                                ->disabled(fn (Get $get) => !$get('boleh_edit'))
                                ->default(function () {
                                    $numbers = \App\Models\PendaftarLingkungan::where('is_free', 0)
                                        ->pluck('no_pendaftar')
                                        ->map(fn($n) => (int) preg_replace('/\D/', '', $n))
                                        ->filter()
                                        ->sort()
                                        ->values();

                                    $expected = 1;
                                    foreach ($numbers as $n) {
                                        if ($n > $expected) break;
                                        $expected++;
                                    }
                                    return (string) $expected;
                                })
                                ->dehydrated(true)
                                ->live()
                                ->suffixAction(
                                    \Filament\Actions\Action::make('manual_edit')
                                        ->icon('heroicon-o-lock-open')
                                        ->iconButton()
                                        ->action(function (Set $set, Get $get) {
                                            $current = (bool) $get('boleh_edit');
                                            $set('boleh_edit', !$current);
                                            $set('is_free', !$current ? 1 : 0);
                                        })
                                        ->color(fn (Get $get) => $get('boleh_edit') ? 'success' : 'green')
                                )
                                ->extraAttributes(fn (Get $get) => [
                                    'title' => 'No. Pendaftar: ' . ($get('no_pendaftar') ?: 'Auto'),
                                ]),

                            Hidden::make('boleh_edit')
                                ->default(false)
                                ->live()
                                ->dehydrated(false)
                                ->afterStateHydrated(function (Set $set, Get $get) {
                                    $set('boleh_edit', (bool) $get('is_free'));
                                }),

                            Hidden::make('is_free')
                                ->default(0),


                    DatePicker::make('tanggal_pendaftar')
                        ->label('Tanggal Pendaftaran')
                        ->required()
                        ->default(now())
                        ->extraAttributes(fn (Get $get) => [
                            'title' => 'Tanggal: ' . ($get('tanggal_pendaftar') ? date('d/m/Y', strtotime($get('tanggal_pendaftar'))) : date('d/m/Y')),
                        ]),

                    TextInput::make('no_hp')
                        ->label('Nomor Ponsel')
                        ->placeholder('628xxxxxxxx')
                        ->required()
                        ->maxLength(128)
                        ->tel()
                        ->prefixIcon('heroicon-o-phone')
                        ->extraAttributes(fn (Get $get) => [
                            'title' => 'No. HP: ' . ($get('no_hp') ?: '-'),
                        ]),
                ]),

            Grid::make(['default' => 1, 'lg' => 3])
                ->schema([


                    TextInput::make('nama_pengirim')
                        ->label('Nama Pendaftar')
                        ->required()
                        ->maxLength(128)
                        ->placeholder('Personal atau perusahaan.')
                        ->prefixIcon('heroicon-o-user')
                        ->extraAttributes(fn (Get $get) => [
                            'title' => 'Nama: ' . ($get('nama_pengirim') ?: '-'),
                        ]),                        

                    TextInput::make('alamat_pengirim')
                        ->label('Alamat Pendaftar')
                        ->required()
                        ->columnSpan(2)
                        ->maxLength(256)
                        ->placeholder('Alamat lengkap personal maupun perusahaan.')
                        ->prefixIcon('heroicon-o-map-pin')
                        ->extraAttributes(fn (Get $get) => [
                            'title' => 'Alamat: ' . ($get('alamat_pengirim') ?: '-'),
                        ]),
                ]),

            // Data Sampling
            Grid::make(['default' => 1, 'sm' => 3, 'lg' => 4])
                ->schema([
                    TextInput::make('petugas_sampling')
                        ->label('Petugas Sampling')
                        ->required()
                        ->maxLength(128)
                        ->placeholder('Nama petugas')
                        ->prefixIcon('heroicon-o-identification')
                        ->extraAttributes(fn (Get $get) => [
                            'title' => 'Petugas: ' . ($get('petugas_sampling') ?: '-'),
                        ]),

                    Select::make('jenis_sampling')
                        ->label('Jenis Sampel')
                        ->relationship('jenisSampel', 'nama_sampel')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->prefixIcon('heroicon-o-clipboard-document-list')
                        ->extraAttributes(fn (Get $get, $record) => [
                            'title' => 'Jenis Sampel: ' . ($record?->jenisSampel?->nama_sampel ?: '-'),
                        ]),

                    DatePicker::make('tanggal_sampling')
                        ->required()
                        ->label('Tanggal Sampling')
                        ->default(now())
                        ->extraAttributes(fn (Get $get) => [
                            'title' => 'Tanggal: ' . ($get('tanggal_sampling') ? date('d/m/Y', strtotime($get('tanggal_sampling'))) : date('d/m/Y')),
                        ]),

                    TimePicker::make('waktu_sampling')
                        ->label('Waktu Sampling')
                        ->default('00:00')          // 👈 default jam 00:00
                        ->seconds(false)            // 👈 hanya jam & menit
                        ->format('H:i')             // 👈 24 jam (16:40)
                        ->extraAttributes(fn (Get $get) => [
                            'title' => 'Waktu: ' . ($get('waktu_sampling') ?? '00:00'),
                        ]),

                ]),

            Grid::make(['default' => 1, 'lg' => 3])
                ->schema([

                    TextInput::make('titik_sampling')
                        ->label('Titik Sampling')
                        ->required()
                        ->placeholder('Cth: Kran Utama, Pipa A, dll.')
                        ->prefixIcon('heroicon-o-map')
                        ->extraAttributes(fn (Get $get) => [
                            'title' => 'Titik: ' . ($get('titik_sampling') ?: '-'),
                        ]),

                    TextInput::make('alamat_sampling')
                        ->label('Alamat Sampling')
                        ->required()
                        ->columnspan(2)
                        ->maxLength(128)
                        ->placeholder('Alamat lengkap lokasi sampling')
                        ->prefixIcon('heroicon-o-map-pin')
                        ->extraAttributes(fn (Get $get) => [
                            'title' => 'Lokasi: ' . ($get('alamat_sampling') ?: '-'),
                        ]),

                ]),

            // Parameter & Regulasi
            Grid::make(['default' => 1, 'lg' => 4])
                ->schema([

                    // Pilihan Regulasi
                    Select::make('regulasi_id')
                        ->label('Regulasi')
                        ->options(\App\Models\Regulasi::pluck('nama_regulasi', 'id'))
                        ->searchable()
                        ->required()
                        ->live()
                        ->preload()
                        ->columnSpanFull()
                        ->prefixIcon('heroicon-o-scale')
                        ->hint('Harus Pilih Salah Satu Regulasi')
                        ->afterStateUpdated(function (Set $set, $state) {
                            // Reset paket dan parameter ketika regulasi berubah
                            $set('paket_id', null);
                            $set('parameter', null);
                        })
                        ->extraAttributes(fn (Get $get) => [
                            'title' => 'Regulasi: ' . (\App\Models\Regulasi::find($get('regulasi_id'))?->nama_regulasi ?: '-'),
                        ]),

                    // Pilihan Mode Parameter (Manual / Paket)
            Radio::make('mode_parameter')
                ->label('Pilih Parameter')
                ->options([
                    true => 'Manual Parameter',
                    false => 'Paket Parameter',
                ])
                ->descriptions([
                    'true' => 'Pilih Parameter Satu per satu',
                    'false' => 'Paket Parameter siap pakai',
                ])
                ->inline(false)
                ->live()
                ->dehydrated(false) // tetap boleh false jika tidak mau disimpan
                ->afterStateHydrated(function (Set $set, $record) {
                    if ($record) {
                        $set('mode_parameter', is_null($record->paket_id));
                    } else {
                        $set('mode_parameter', true);
                    }
                })
                ->afterStateUpdated(function (bool $state, Set $set, $record) {
                    if ($state) { // Manual
                        $set('paket_id', null);
                    } else { // Paket
                        $set('parameter', null);
                    }
                }),


                                // Paket Parameter Option
                                Select::make('paket_id')
                                    ->label('Paket Parameter')
                                    ->options(function (Get $get) {
                                        $regulasiId = $get('regulasi_id');
                                        return $regulasiId
                                            ? \App\Models\PaketParameter::where('id_regulasi', $regulasiId)
                                                ->pluck('nama_paket', 'id')
                                            : [];
                                    })
                                    ->visible(fn (Get $get) => !$get('mode_parameter'))
                                    // ->required(fn (Get $get) => !$get('mode_parameter'))
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->prefixIcon('heroicon-o-cube')
                ->afterStateUpdated(function ($state, Set $set, $record) {
                    if (!empty($state)) {
                        $set('parameter', null);
                        $set('mode_parameter', false);
                    }
                })
                                    ->extraAttributes(fn (Get $get) => [
                                        'title' => 'Paket: ' . (\App\Models\PaketParameter::find($get('paket_id'))?->nama_paket ?: '-'),
                                    ])
                                    ->columnSpan(1),

                                // Detail Paket (List Parameter & Total Harga)
                                Placeholder::make('paket_detail')
                                    ->label('Detail Paket')
                                    ->visible(fn (Get $get) => !$get('mode_parameter') && $get('paket_id'))
                                    ->content(function (Get $get) {
                                        $paketId = $get('paket_id');
                                        if (!$paketId) return '';

                                        $paket = \App\Models\PaketParameter::find($paketId);
                                        if (!$paket) return '';

                                        $parameterIds = is_string($paket->parameter)
                                            ? json_decode($paket->parameter, true)
                                            : $paket->parameter;

                                        if (empty($parameterIds) || $parameterIds === '[]') {
                                            return new \Illuminate\Support\HtmlString('
                                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                                    Tidak ada parameter dalam paket ini
                                                </div>
                                            ');
                                        }

                                        $parameters = \App\Models\ParameterLingkungan::whereIn('id', $parameterIds)->get();

                                        // ✅ Perbaikan: list jadi 2 kolom rapi (dengan inline CSS agar tidak ditimpa Filament)
                                        $list = '<ul class="list-disc list-inside text-sm text-gray-700 dark:text-gray-300"
                                            style="columns:2; column-gap:2rem; -webkit-columns:2; -moz-columns:2;">';
                                        foreach ($parameters as $param) {
                                            $list .= '<li>' . e($param->nama_parameter) . '</li>';
                                        }
                                        $list .= '</ul>';

                                        $totalHarga = number_format($paket->total_harga ?? 0, 0, ',', '.');

                                        return new \Illuminate\Support\HtmlString('
                                            <div class="space-y-2 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                                <div class="font-medium text-sm text-gray-700 dark:text-gray-300">
                                                    Parameter dalam paket:
                                                </div>
                                                ' . $list . '
                                                <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                                                    <span class="font-semibold text-primary-600 dark:text-primary-400">
                                                        Total Harga: Rp ' . $totalHarga . '
                                                    </span>
                                                </div>
                                            </div>
                                        ');
                                    })
                                    ->columnSpan(2),

                                    // Manual Parameter Option
                                    Select::make('parameter')
                                        ->label('Parameter Pengujian')
                                        ->multiple()
                                        ->searchable()
                                        ->visible(fn (Get $get) => $get('mode_parameter'))
                                        // ->required(fn (Get $get) => $get('mode_parameter'))
                                        ->options(function (Get $get, $record = null) {
                                            $id = $get('regulasi_id') ?? $record?->regulasi_id;
                                            return $id
                                                ? \App\Models\ParameterLingkungan::where('id_regulasi', $id)
                                                    ->pluck('nama_parameter', 'id')
                                                    ->toArray()
                                                : [];
                                        })
                                        ->live()
                                        ->prefixIcon('heroicon-o-list-bullet')
                ->afterStateUpdated(function ($state, Set $set, $record) {
                    if (!empty($state)) {
                        $set('paket_id', null);
                        $set('mode_parameter', true);
                    }
                })
                                        
                                        ->getSearchResultsUsing(function (string $search, Get $get) {
                                            $id = $get('regulasi_id');
                                            return $id
                                                ? \App\Models\ParameterLingkungan::where('id_regulasi', $id)
                                                    ->where('nama_parameter', 'like', "%{$search}%")
                                                    ->limit(20)
                                                    ->pluck('nama_parameter', 'id')
                                                    ->toArray()
                                                : [];
                                        })
                                        ->extraAttributes(fn (Get $get) => [
                                            'title' => 'Parameter: ' . (is_array($get('parameter')) ? count($get('parameter')) . ' dipilih' : '-'),
                                        ])
                                        ->columnSpan(2),

                                    // Total Harga Manual
                                    Placeholder::make('parameter_total')
                                        ->label('Total Harga')
                                        ->visible(fn (Get $get) => $get('mode_parameter') && !empty($get('parameter')))
                                        ->content(function (Get $get) {
                                            $parameterIds = $get('parameter');
                                            if (empty($parameterIds)) return 'Rp 0';

                                            // Handle jika parameter masih string JSON
                                            if (is_string($parameterIds)) {
                                                $parameterIds = json_decode($parameterIds, true);
                                            }

                                            if (empty($parameterIds) || $parameterIds === '[]') return 'Rp 0';

                                            $total = \App\Models\ParameterLingkungan::whereIn('id', $parameterIds)
                                                ->sum('harga_parameter');

                                            $formatted = number_format($total, 0, ',', '.');

                                            return new \Illuminate\Support\HtmlString('
                                                <div class="text-lg font-semibold text-primary-600 dark:text-primary-400">
                                                    Rp ' . $formatted . '
                                                </div>
                                            ');
                                        })
                                        ->columnSpan(1),
                                    ]),
                                    // Keterangan & Catatan (Toggle - Default Tertutup)
                                    Section::make('Informasi Tambahan')
                                        ->schema([
                                            Grid::make(['default' => 1, 'lg' => 2])
                                                ->schema([
                                                    Textarea::make('keterangan')
                                                        ->columnSpan(1)
                                                        ->label('Keterangan')
                                                        ->placeholder('Keterangan tambahan, keterangan ini akan muncul pada lembar hasil (opsional)')
                                                        ->extraAttributes(fn (Get $get) => [
                                                            'title' => 'Keterangan: ' . ($get('keterangan') ?: 'Kosong'),
                                                        ]),

                                                    Textarea::make('catatan')
                                                        ->columnSpan(1)
                                                        ->label('Catatan Internal')
                                                        ->placeholder('Catatan untuk keperluan internal')
                                                        ->extraAttributes(fn (Get $get) => [
                                                            'title' => 'Catatan: ' . ($get('catatan') ?: 'Kosong'),
                                                        ]),
                                                ]),
                                        ])
                                        ->collapsible()
                                        ->collapsed()
                                        ->columnSpanFull(),
                                ])
                                ->columns(1); 
                }
            }
