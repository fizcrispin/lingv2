<?php

namespace App\Filament\Resources\Transaksis\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;


class TransaksiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(12)
                    ->schema([
                        // Seksi Kiri: Informasi & Administrasi
                        Section::make('Informasi Pendaftaran')
                            ->columnSpan(6)
                            ->schema([
                                Select::make('id_pendaftar')
                                    ->label('Pilih Pendaftar')
                                    ->relationship('pendaftar', 'no_pendaftar')
                                    ->required()
                                    ->searchable()
                                    ->placeholder('Pilih berkas pendaftaran...')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $pendaftar = \App\Models\PendaftarLingkungan::find($state);
                                            if ($pendaftar) {
                                                $set('total_harga', $pendaftar->total_harga);
                                            }
                                        }
                                    }),
                                DatePicker::make('tanggal_tagihan')
                                    ->label('Tgl. Tagihan')
                                    ->default(now())
                                    ->required(),
                                Select::make('metode_pembayaran')
                                    ->label('Metode Bayar')
                                    ->options([
                                        'Transfer' => 'Transfer Bank',
                                        'Tunai' => 'Tunai / Cash',
                                        'Lainnya' => 'Lainnya',
                                    ]),
                                TextInput::make('kode_ver')
                                    ->label('No. Referensi')
                                    ->placeholder('TRX-XXXXX'),
                            ]),

                        // Seksi Kanan: Pelunasan & Keuangan
                        Section::make('Status & Pelunasan')
                            ->columnSpan(6)
                            ->schema([
                                Select::make('status_bayar')
                                    ->label('Status Pembayaran')
                                    ->options([
                                        0 => 'Draft',
                                        1 => 'Menunggu Pembayaran',
                                        2 => 'Lunas (Selesai)',
                                    ])
                                    ->required()
                                    ->default(0)
                                    ->selectablePlaceholder(false),
                                TextInput::make('total_harga')
                                    ->label('Total Tagihan')
                                    ->numeric()
                                    ->readOnly()
                                    ->prefix('IDR')
                                    ->extraInputAttributes(['class' => 'font-bold text-danger-600']),

                                TextInput::make('total_bayar')
                                    ->label('Jumlah Dibayar')
                                    ->numeric()
                                    ->prefix('IDR')
                                    ->live(onBlur: true)
                                    ->placeholder('0'),
                                DatePicker::make('tanggal_bayar')
                                    ->label('Tgl. Pelunasan'),
                                Placeholder::make('sisa_tagihan')
                                    ->label('Sisa Tagihan')
                                    ->content(function (Get $get) {
                                        $total = (int) $get('total_harga') ?? 0;
                                        $bayar = (int) $get('total_bayar') ?? 0;
                                        $sisa = max(0, $total - $bayar);
                                        
                                        $color = $sisa > 0 ? 'text-danger-600' : 'text-success-600';
                                        $text = 'IDR ' . number_format($sisa, 0, ',', '.');
                                        
                                        return new \Illuminate\Support\HtmlString("<span class='font-bold {$color} text-lg'>{$text}</span>");
                                    }),
                            ]),

                        Section::make('Catatan')
                            ->columnSpan(12)
                            ->schema([
                                Textarea::make('catatan')
                                    ->label('Catatan Kuitansi')
                                    ->rows(2)
                                    ->placeholder('Tambahkan informasi tambahan jika ada...'),
                            ]),
                    ]),
            ])
            ->columns(1);
            
    }
}
