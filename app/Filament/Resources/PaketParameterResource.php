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
                    ->required(),
                Forms\Components\Select::make('parameter')
                    ->label('Parameter')
                    ->multiple()
                    ->options(\App\Models\ParameterLingkungan::pluck('nama_parameter', 'id'))
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_paket')
                    ->label('Nama Paket')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('regulasi.nama_regulasi') // Assuming relation name
                    ->label('Regulasi')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
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
