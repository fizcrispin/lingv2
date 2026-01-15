<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaketParameterResource\Pages;
use App\Models\PaketParameter;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class PaketParameterResource extends Resource
{
    protected static ?string $model = PaketParameter::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';
    
    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Paket Parameter';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('nama_paket')
                    ->label('Nama Paket')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('id_regulasi')
                    ->label('Regulasi')
                    ->options(\App\Models\Regulasi::pluck('nama_regulasi', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('parameter', [])),
                Forms\Components\Select::make('parameter')
                    ->label('Parameter')
                    ->multiple()
                    ->options(fn (Get $get) => \App\Models\ParameterLingkungan::where('id_regulasi', $get('id_regulasi'))->pluck('nama_parameter', 'id'))
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        $set('total_harga', \App\Models\ParameterLingkungan::whereIn('id', $state ?? [])->sum('harga_parameter'));
                    }),
                Forms\Components\TextInput::make('total_harga')
                    ->label('Total Harga')
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\ToggleButtons::make('option')
                    ->label('Status')
                    ->options([
                        '2' => 'Aktif',
                        '1' => 'Tidak Aktif',
                    ])
                    ->colors([
                        '2' => 'success',
                        '1' => 'danger',
                    ])
                    ->icons([
                        '2' => 'heroicon-o-check-circle',
                        '1' => 'heroicon-o-x-circle',
                    ])
                    ->default('2')
                    ->inline(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_paket')
                    ->label('Nama Paket')
                    ->searchable()
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->nama_paket)
                    ->sortable(),
                Tables\Columns\TextColumn::make('regulasi.nama_regulasi') // Assuming relation name
                    ->label('Regulasi')
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->regulasi->nama_regulasi)
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('option')
                    ->label('Option')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '1' => 'Tidak Aktif',
                        '2' => 'Aktif',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        '1' => 'danger',
                        '2' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make()
                ->label(false),
                \Filament\Actions\DeleteAction::make()
                ->label(false),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePaketParameters::route('/'),
        ];
    }
}
