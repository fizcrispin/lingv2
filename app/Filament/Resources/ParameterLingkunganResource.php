<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ParameterLingkunganResource\Pages;
use App\Models\ParameterLingkungan;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class ParameterLingkunganResource extends Resource
{
    protected static ?string $model = ParameterLingkungan::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-beaker';
    
    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Parameter Lingkungan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('id_regulasi')
                    ->label('Regulasi')
                    ->relationship('regulasi', 'nama_regulasi') // Assuming relation name 'regulasi' exists in model or I use direct query
                    ->options(\App\Models\Regulasi::pluck('nama_regulasi', 'id'))
                    ->searchable(),
                Forms\Components\Select::make('kategori')
                    ->label('Kategori')
                    ->options(\App\Models\Kategori::pluck('nama_kategori', 'id')) // Using 'id' as key
                    ->searchable(),
                Forms\Components\TextInput::make('nama_parameter')
                    ->label('Nama Parameter')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('harga_parameter')
                    ->label('Harga')
                    ->numeric()
                    ->prefix('Rp'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_parameter')
                    ->label('Nama Parameter')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kategoriData.nama_kategori') // Model has 'kategoriData' relation
                    ->label('Kategori')
                    ->sortable(), 
                Tables\Columns\TextColumn::make('harga_parameter')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('kategori')
                     ->options(\App\Models\Kategori::pluck('nama_kategori', 'id')),
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
            'index' => Pages\ManageParameterLingkungans::route('/'),
        ];
    }
}
