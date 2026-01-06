<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EkspedisiResource\Pages;
use App\Models\Ekspedisi;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;

use BackedEnum;

class EkspedisiResource extends Resource
{
    protected static ?string $model = Ekspedisi::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';
    
    protected static string|\UnitEnum|null $navigationGroup = 'Operasional';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Ekspedisi';
    protected static ?string $slug = 'ekspedisi';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Section::make('Informasi Pendaftaran')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        \Filament\Schemas\Components\Group::make()->schema([
                            Forms\Components\TextInput::make('id_pendaftar_view')
                                ->label('Nomor')
                                ->prefixIcon('heroicon-o-hashtag')
                                ->disabled()
                                ->dehydrated(false)
                                ->formatStateUsing(fn ($record) => $record?->pendaftarLingkungan?->id_pendaftar ?? '-')
                                ->extraInputAttributes(fn ($state) => ['title' => $state]),

                            Forms\Components\TextInput::make('tanggal_pendaftar_view')
                                ->label('Tanggal')
                                ->disabled()
                                ->dehydrated(false)
                                ->formatStateUsing(fn ($record) => $record?->pendaftarLingkungan?->tanggal_pendaftar ? \Carbon\Carbon::parse($record->pendaftarLingkungan->tanggal_pendaftar)->format('d-M-y') : '-')
                                ->extraInputAttributes(fn ($state) => ['title' => $state]),
                        ])->columns(2),

                        Forms\Components\TextInput::make('nama_pengirim_view')
                            ->label('Nama Pengirim')
                            ->prefixIcon('heroicon-o-user')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->formatStateUsing(fn ($record) => $record?->pendaftarLingkungan?->nama_pengirim ?? '-')
                            ->extraInputAttributes(fn ($state) => ['title' => $state]),

                        Forms\Components\TextInput::make('titik_sampling_view')
                            ->label('Titik Sampling')
                            ->prefixIcon('heroicon-o-map-pin')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->formatStateUsing(fn ($record) => $record?->pendaftarLingkungan?->titik_sampling ?? '-')
                            ->extraInputAttributes(fn ($state) => ['title' => $state]),

                        \Filament\Schemas\Components\Actions::make([
                            \Filament\Actions\Action::make('preview_hasil')
                                ->label('Preview Hasil')
                                ->icon('heroicon-o-printer')
                                ->url(fn ($record) => $record?->pendaftarLingkungan ? route('cetak.hasil', ['record' => $record->pendaftarLingkungan->id]) : '#')
                                ->openUrlInNewTab()
                                ->color('success'),
                        ])->fullWidth(),
                    ])
                    ->collapsible(),

                \Filament\Schemas\Components\Section::make('Status Ekspedisi')
                    ->icon('heroicon-o-truck')
                    ->schema([
                        \Filament\Schemas\Components\Fieldset::make('Penerimaan Sampel')
                            ->schema([
                                Forms\Components\Toggle::make('sampel_diterima')
                                    ->onIcon('heroicon-m-check')
                                    ->offIcon('heroicon-m-x-mark')
                                    ->live(), // Make reactive if needed
                                
                                Forms\Components\DateTimePicker::make('tanggal_diterima')
                                    ->placeholder('Pilih waktu...'),
                            ])->columns(1),

                        \Filament\Schemas\Components\Fieldset::make('Verifikasi & Validasi')
                            ->schema([
                                Forms\Components\Toggle::make('verifikasi_hasil')
                                    ->label('Verif.')
                                    ->onIcon('heroicon-m-check')
                                    ->offIcon('heroicon-m-x-mark')
                                    ->inline(false),
                                
                                Forms\Components\Toggle::make('validasi1')
                                    ->label('Valid. 1')
                                    ->onIcon('heroicon-m-check')
                                    ->offIcon('heroicon-m-x-mark')
                                    ->inline(false),

                                Forms\Components\Toggle::make('validasi2')
                                    ->label('Valid. 2')
                                    ->onIcon('heroicon-m-check')
                                    ->offIcon('heroicon-m-x-mark')
                                    ->inline(false),
                            ])->columns(3),

                        \Filament\Schemas\Components\Section::make()
                            ->schema([
                                Forms\Components\Toggle::make('sampel_dimusnahkan')
                                    ->label('Sampel Dimusnahkan')
                                    ->onIcon('heroicon-m-trash')
                                    ->offIcon('heroicon-m-minus')
                                    ->onColor('danger'),
                            ])->compact(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pendaftarLingkungan.no_pendaftar')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->weight('bold')
                    ->copyable() 
                    ->fontFamily('mono') // Enterprise data style
                    ->action(EditAction::make()->modalWidth('2xl')->slideOver()),

                Tables\Columns\TextColumn::make('pendaftarLingkungan.nama_pengirim')
                    ->label('Nama Pengirim')
                    ->searchable()
                    ->limit(25)
                    ->color('gray')
                    ->tooltip(fn ($record) => $record->pendaftarLingkungan->nama_pengirim)
                    ->action(EditAction::make('edit_nama')->modalWidth('2xl')->slideOver()),
                
                Tables\Columns\TextColumn::make('pendaftarLingkungan.titik_sampling')
                    ->label('Titik Sampling')
                    ->searchable()
                    ->limit(20)
                    ->color('gray')
                    ->tooltip(fn ($record) => $record->pendaftarLingkungan->titik_sampling)
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('pendaftarLingkungan.tanggal_pendaftar')
                    ->label('Tgl Daftar')
                    ->date('d M Y')
                    ->sortable()
                    ->color('gray')
                    ->size('sm'),
                
                Tables\Columns\IconColumn::make('sampel_diterima')
                    ->label('Diterima')
                    ->boolean()
                    ->trueIcon('heroicon-c-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->action(function($record) {
                        return \Filament\Actions\Action::make('toggle_diterima')
                            ->requiresConfirmation()
                            ->modalHeading('Update Penerimaan')
                            ->action(fn() => $record->update(['sampel_diterima' => !$record->sampel_diterima]));
                    }),
                    
                Tables\Columns\TextColumn::make('tanggal_diterima')
                    ->label('Tgl Diterima')
                    ->dateTime('d/m H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->size('xs')
                    ->color('gray')
                    ->fontFamily('mono'),

                Tables\Columns\IconColumn::make('verifikasi_hasil')
                    ->label('Verif.')
                    ->boolean()
                    ->trueIcon('heroicon-c-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->action(function($record) {
                        return \Filament\Actions\Action::make('toggle_verif')
                            ->requiresConfirmation()
                            ->action(fn() => $record->update(['verifikasi_hasil' => !$record->verifikasi_hasil]));
                    }),

                Tables\Columns\IconColumn::make('validasi1')
                    ->label('Val 1')
                    ->boolean()
                    ->trueIcon('heroicon-c-check-badge')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->action(function($record) {
                        return \Filament\Actions\Action::make('toggle_val1')
                            ->requiresConfirmation()
                            ->action(fn() => $record->update(['validasi1' => !$record->validasi1]));
                    }),

                Tables\Columns\IconColumn::make('validasi2')
                    ->label('Val 2')
                    ->boolean()
                    ->trueIcon('heroicon-c-check-badge')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->action(function($record) {
                        return \Filament\Actions\Action::make('toggle_val2')
                            ->requiresConfirmation()
                            ->action(fn() => $record->update(['validasi2' => !$record->validasi2]));
                    }),
                    
                Tables\Columns\IconColumn::make('sampel_dimusnahkan')
                    ->label('Musnah')
                    ->boolean()
                    ->trueIcon('heroicon-c-trash')
                    ->falseIcon('heroicon-o-archive-box')
                    ->trueColor('danger')
                    ->falseColor('success') // Green archive means safe/stored
                    ->action(function($record) {
                        return \Filament\Actions\Action::make('toggle_musnah')
                            ->requiresConfirmation()
                            ->color('danger')
                            ->action(fn() => $record->update(['sampel_dimusnahkan' => !$record->sampel_dimusnahkan]));
                    }),

                Tables\Columns\TextInputColumn::make('keterangan')
                    ->label('Keterangan')
                    ->extraAttributes(['class' => 'min-w-[150px]']),
            ])
            ->striped()
            ->filters([
                //
            ])
            ->actions([
                // Actions attached to columns
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('bulk_edit')
                        ->label('Edit Masal')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->form([
                            \Filament\Schemas\Components\Section::make('Penerimaan Sampel')
                                ->schema([
                                    Forms\Components\Select::make('sampel_diterima')
                                        ->label('Sampel Diterima?')
                                        ->options([
                                            1 => 'Ya (Diterima)',
                                            0 => 'Tidak / Belum',
                                        ])
                                        ->placeholder('Biarkan Jika Tidak Mengubah'),
                                    Forms\Components\DateTimePicker::make('tanggal_diterima')
                                        ->label('Tanggal Diterima')
                                        ->placeholder('Biarkan Jika Tidak Mengubah'),
                                ])->columns(2),

                            \Filament\Schemas\Components\Section::make('Verifikasi & Validasi')
                                ->schema([
                                    Forms\Components\Select::make('verifikasi_hasil')
                                        ->label('Verifikasi Hasil')
                                        ->options([
                                            1 => 'Sudah Verifikasi',
                                            0 => 'Belum Verifikasi',
                                        ])
                                        ->placeholder('-'),
                                    Forms\Components\Select::make('validasi1')
                                        ->label('Validasi 1')
                                        ->options([
                                            1 => 'Sudah Validasi 1',
                                            0 => 'Belum Validasi 1',
                                        ])
                                        ->placeholder('-'),
                                    Forms\Components\Select::make('validasi2')
                                        ->label('Validasi 2')
                                        ->options([
                                            1 => 'Sudah Validasi 2',
                                            0 => 'Belum Validasi 2',
                                        ])
                                        ->placeholder('-'),
                                ])->columns(3),

                            \Filament\Schemas\Components\Section::make('Pemusnahan')
                                ->schema([
                                    Forms\Components\Select::make('sampel_dimusnahkan')
                                        ->label('Sampel Dimusnahkan?')
                                        ->options([
                                            1 => 'Ya (Dimusnahkan)',
                                            0 => 'Tidak',
                                        ])
                                        ->placeholder('-'),
                                ])->compact(),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                            // Filter data yang tidak diisi (null) agar tidak menimpa data lama dengan null
                            $dataToUpdate = array_filter($data, fn ($value) => $value !== null);

                            if (empty($dataToUpdate)) {
                                return;
                            }

                            $records->each(fn ($record) => $record->update($dataToUpdate));

                            \Filament\Notifications\Notification::make()
                                ->title('Berhasil memperbarui ' . $records->count() . ' data ekspedisi')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEkspedisis::route('/'),
        ];
    }
}
