<?php

namespace App\Filament\Resources\Transaksis\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use Filament\Support\Colors\Color;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class TransaksisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('pendaftar.no_pendaftar')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('pendaftar.nama_pengirim')
                    ->label('Nama Pengirim')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->pendaftar?->nama_pengirim)
                    ->placeholder('-'),
                TextColumn::make('pendaftar.jenisSampel.nama_sampel')
                    ->label('Jenis Sampel')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->pendaftar?->jenisSampel?->nama_sampel)
                    ->placeholder('-'),
                TextColumn::make('pendaftar.titik_sampling')
                    ->label('Titik Sampling')
                    ->searchable()
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->pendaftar?->titik_sampling)
                    ->placeholder('-'),
                TextColumn::make('tanggal_tagihan')
                    ->label('Tagihan/Bayar')
                    ->html()
                    ->formatStateUsing(function ($record) {
                        $tagihan = $record->tanggal_tagihan ? $record->tanggal_tagihan->format('d/m/Y') : '-';
                        $bayar = $record->tanggal_bayar ? $record->tanggal_bayar->format('d/m/Y') : '-';
                        return "
                            <div style='display: flex; flex-direction: column;'>
                                <span style='font-weight: bold; color: #dc2626;' title='Tanggal Tagihan'>{$tagihan}</span>
                                <span style='font-weight: bold; color: #16a34a; margin-top: 4px;' title='Tanggal Bayar'>{$bayar}</span>
                            </div>
                        ";
                    }),
                TextColumn::make('total_harga')
                    ->label('Tagihan/Bayar')
                    ->html()
                    ->alignment('right')
                    ->formatStateUsing(function ($record) {
                        $tagihan = 'IDR ' . number_format($record->total_harga, 0, ',', '.');
                        $bayar = 'IDR ' . number_format($record->total_bayar, 0, ',', '.');
                        return "
                            <div style='display: flex; flex-direction: column; align-items: flex-end;'>
                                <span style='font-weight: bold; color: #dc2626;' title='Tagihan'>{$tagihan}</span>
                                <span style='font-weight: bold; color: #16a34a; margin-top: 4px;' title='Sudah Dibayar'>{$bayar}</span>
                            </div>
                        ";
                    }),
                TextColumn::make('status_bayar')
                    ->label('Status')
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        0 => 'gray',
                        1 => 'warning',
                        2 => 'success',
                        default => 'primary',
                    })
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        0 => 'Draft',
                        1 => 'Menunggu Pembayaran',
                        2 => 'Lunas',
                        default => (string) $state,
                    }),
                TextColumn::make('metode_pembayaran')
                    ->label('Metode')
                    ->placeholder('-'),
            ])

            ->filters([
                Filter::make('tanggal_tagihan')
                    ->form([
                        DatePicker::make('dari')
                            ->label('Dari Tanggal'),
                        DatePicker::make('sampai')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_tagihan', '>=', $date),
                            )
                            ->when(
                                $data['sampai'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_tagihan', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['dari'] ?? null) {
                            $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['dari'])->format('d/m/Y');
                        }
                        if ($data['sampai'] ?? null) {
                            $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['sampai'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),
                SelectFilter::make('status_bayar')
                    ->label('Status')
                    ->options([
                        0 => 'Draft',
                        1 => 'Menunggu Pembayaran',
                        2 => 'Lunas',
                    ]),
                SelectFilter::make('metode_pembayaran')
                    ->label('Metode')
                    ->options([
                        'Transfer' => 'Transfer Bank',
                        'Tunai' => 'Tunai / Cash',
                        'Lainnya' => 'Lainnya',
                    ]),
            ])
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->modalWidth('7xl'),
                Action::make('cetak_faktur')
                    ->label('Faktur')
                    ->tooltip('Cetak Faktur Penagihan')
                    ->iconButton()
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->visible(fn ($record) => $record->pendaftar()->exists())
                    ->url(fn ($record) => route('print.transaksi', ['id' => $record->id, 'type' => 'faktur']))
                    ->openUrlInNewTab(),
                Action::make('whatsapp_notif')
                    ->label('WhatsApp')
                    ->tooltip('Kirim Notifikasi Tagihan WA')
                    ->iconButton()
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->visible(fn ($record) => $record->pendaftar()->exists() && $record->pendaftar->no_hp)
                    ->url(function ($record) {
                        $pendaftar = $record->pendaftar;
                        $number = $pendaftar->no_hp;
                        
                        // Format nomor HP (hapus non-digit, ganti 0 di depan dengan 62)
                        $number = preg_replace('/[^0-9]/', '', $number);
                        if (substr($number, 0, 1) === '0') {
                            $number = '62' . substr($number, 1);
                        }

                        $name = $pendaftar->nama_pengirim;
                        $jenisSampel = $pendaftar->jenisSampel->nama_sampel ?? '-';
                        $titikSampling = $pendaftar->titik_sampling; // Asumsi field ini sudah benar
                        
                        // Kode Bayar: ymd + no_pendaftar
                        // Contoh: 2601061
                        $dateRef = $pendaftar->tanggal_pendaftar ?? now();
                        if (is_string($dateRef)) {
                            $dateRef = \Carbon\Carbon::parse($dateRef);
                        }
                        $dateCode = $dateRef->format('ymd');
                        
                        $kodeBayar = $dateCode . $pendaftar->no_pendaftar; 
                        
                        $totalBiaya = 'Rp. ' . number_format($record->total_harga, 0, ',', '.');

                        $message = "===========================\n" .
                            "Yth Bapak/Ibu {$name}\n\n" .
                            "Kami dari Laboratorium Kesehatan Kab. Sragen, menyampaikan informasi tagihan pemeriksaan laboratorium.\n\n" .
                            "Jenis Sampel : {$jenisSampel}\n" .
                            "Titik Sampling : {$titikSampling}\n" .
                            "Kode Bayar : {$kodeBayar}\n" .
                            "Total Biaya : {$totalBiaya}\n\n" .
                            "Pembayaran harap paling lambat 14 hari setelah informasi ini terkirim.\n\n" .
                            "Atas Perhatiannya kami ucapkan terima kasih.\n" .
                            "===========================";

                        $encodedMessage = urlencode($message);
                        return "https://wa.me/{$number}?text={$encodedMessage}";
                    })
                    ->openUrlInNewTab(),
                Action::make('cetak_invoice')
                    ->label('Kuitansi')
                    ->tooltip('Cetak Kuitansi Layanan')
                    ->iconButton()
                    ->icon('heroicon-o-document-text')
                    ->color('warning')
                    ->visible(fn ($record) => $record->pendaftar()->exists())
                    ->url(fn ($record) => route('print.transaksi', ['id' => $record->id, 'type' => 'kuitansi']))
                    ->openUrlInNewTab(),
                Action::make('cetak_kuitansi')
                    ->label('Bukti Bayar')
                    ->tooltip('Cetak Bukti Pembayaran / Kuitansi')
                    ->iconButton()
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn ($record) => $record->status_bayar === 2 && $record->pendaftar()->exists())
                    ->url(fn ($record) => route('print.transaksi', ['id' => $record->id, 'type' => 'bukti_bayar']))
                    ->openUrlInNewTab(),
            ])
            ->actionsPosition(\Filament\Tables\Enums\RecordActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('bulk_edit')
                        ->label('Edit Masal')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->form([
                            \Filament\Forms\Components\Select::make('status_bayar')
                                ->label('Status Pembayaran')
                                ->options([
                                    0 => 'Draft',
                                    1 => 'Menunggu Pembayaran',
                                    2 => 'Lunas (Selesai)',
                                ]),
                            \Filament\Forms\Components\Select::make('metode_pembayaran')
                                ->label('Metode Pembayaran')
                                ->options([
                                    'Transfer' => 'Transfer Bank',
                                    'Tunai' => 'Tunai / Cash',
                                    'Lainnya' => 'Lainnya',
                                ]),
                            \Filament\Forms\Components\DatePicker::make('tanggal_bayar')
                                ->label('Tanggal Pelunasan'),
                            \Filament\Forms\Components\TextInput::make('total_bayar')
                                ->label('Jumlah Dibayar')
                                ->numeric()
                                ->prefix('IDR')
                                ->placeholder('Isi jika ingin menyamakan nominal'),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                            // Data update dasar
                            $updateData = array_filter([
                                'status_bayar' => $data['status_bayar'],
                                'metode_pembayaran' => $data['metode_pembayaran'],
                                'tanggal_bayar' => $data['tanggal_bayar'],
                            ], fn($value) => $value !== null);

                            $nominalDibayar = $data['total_bayar'];
                            $notifNominalSkipped = false;

                            // Jika ada nominal yang ingin di-bulk
                            if ($nominalDibayar !== null && $nominalDibayar !== '') {
                                // Cek apakah semua record memiliki total_harga yang sama
                                $uniqueTotalHarga = $records->pluck('total_harga')->unique();
                                
                                if ($uniqueTotalHarga->count() === 1) {
                                    $updateData['total_bayar'] = $nominalDibayar;
                                } else {
                                    $notifNominalSkipped = true;
                                }
                            }

                            if (empty($updateData) && !$notifNominalSkipped) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Tidak ada data yang diubah')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $records->each(fn($record) => $record->update($updateData));

                            $mainNotif = \Filament\Notifications\Notification::make()
                                ->title('Berhasil memperbarui ' . $records->count() . ' transaksi')
                                ->success();

                            if ($notifNominalSkipped) {
                                $mainNotif->body('Catatan: Nominal Bayar diabaikan karena Transaksi yang dipilih memiliki nilai Tagihan yang berbeda-beda.');
                                $mainNotif->warning(); // Ubah ke warning jika ada yang di-skip
                            }

                            $mainNotif->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}
