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
                    ->schema([
                        Forms\Components\TextInput::make('pendaftarLingkungan.no_pendaftar')
                            ->label('No Pendaftar')
                            ->disabled(),
                        Forms\Components\DatePicker::make('pendaftarLingkungan.tanggal_sampling')
                            ->label('Tanggal Sampling')
                            ->disabled()
                            ->format('d/m/Y'),
                        Forms\Components\TimePicker::make('pendaftarLingkungan.waktu_sampling')
                            ->label('Waktu Sampling')
                            ->disabled(),
                        Forms\Components\TextInput::make('pendaftarLingkungan.nama_pengirim')
                            ->label('Nama Pengirim')
                            ->disabled(),
                        Forms\Components\TextInput::make('pendaftarLingkungan.titik_sampling')
                            ->label('Titik Sampling')
                            ->disabled(),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('Data Ekspedisi')
                    ->schema([
                        Forms\Components\Toggle::make('sampel_diterima')
                            ->label('Sampel Diterima')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('tanggal_diterima')
                            ->label('Tanggal Diterima'),
                        Forms\Components\Toggle::make('verifikasi_hasil')
                            ->label('Verifikasi Hasil')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark'),
                        Forms\Components\Toggle::make('validasi1')
                            ->label('Validasi 1')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark'),
                        Forms\Components\Toggle::make('validasi2')
                            ->label('Validasi 2')
                            ->onIcon('heroicon-m-check')
                            ->offIcon('heroicon-m-x-mark'),
                        Forms\Components\Toggle::make('sampel_dimusnahkan')
                            ->label('Sampel Dimusnahkan')
                            ->onIcon('heroicon-m-trash')
                            ->offIcon('heroicon-m-minus'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_pendaftar')
                    ->label('Nomor')
                    ->searchable()
                    ->sortable()
                    ->color('primary')
                    ->weight('bold')
                    ->action(EditAction::make()->modalWidth('2xl')->slideOver()),
                Tables\Columns\TextColumn::make('pendaftarLingkungan.nama_pengirim')
                    ->label('Nama Pengirim')
                    ->searchable()
                    ->limit(10)
                    ->tooltip(fn ($record) => $record->pendaftarLingkungan->nama_pengirim)
                    ->color('primary')
                    ->action(EditAction::make('edit_nama')->modalWidth('2xl')->slideOver()),
                Tables\Columns\TextColumn::make('pendaftarLingkungan.titik_sampling')
                    ->label('Titik Sampling')
                    ->searchable()
                    ->limit(10)
                    ->tooltip(fn ($record) => $record->pendaftarLingkungan->titik_sampling),
                Tables\Columns\TextColumn::make('pendaftarLingkungan.tanggal_sampling')
                    ->label('Tgl Sampling')
                    ->date('d/m/y'),
                
                Tables\Columns\ToggleColumn::make('sampel_diterima')
                    ->label('Diterima')
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark'),
                    
                Tables\Columns\TextColumn::make('tanggal_diterima')
                    ->label('Tgl Diterima')
                    ->dateTime('d/m/y H:i')
                    ->placeholder('Tambah Tanggal')
                    ->tooltip('Klik untuk edit tanggal')
                    ->sortable()
                    ->action(
                        EditAction::make('edit_tanggal')
                            ->form([
                                Forms\Components\DateTimePicker::make('tanggal_diterima')
                                    ->label('Tanggal Diterima'), // Removed required
                            ])
                            ->modalWidth('md')
                            ->tooltip('Klik untuk edit tanggal')
                    ),

                Tables\Columns\ToggleColumn::make('verifikasi_hasil')
                    ->label('Verif. Hasil')
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark'),

                Tables\Columns\ToggleColumn::make('validasi1')
                    ->label('Validasi 1')
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark'),

                Tables\Columns\ToggleColumn::make('validasi2')
                    ->label('Validasi 2')
                    ->onIcon('heroicon-m-check')
                    ->offIcon('heroicon-m-x-mark'),
                    
                Tables\Columns\ToggleColumn::make('sampel_dimusnahkan')
                    ->label('Dimusnahkan')
                    ->onIcon('heroicon-m-trash')
                    ->offIcon('heroicon-m-minus'),

                Tables\Columns\TextInputColumn::make('keterangan')
                    ->label('Keterangan'),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Actions attached to columns
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
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
