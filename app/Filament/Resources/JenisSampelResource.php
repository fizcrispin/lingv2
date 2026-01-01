<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JenisSampelResource\Pages;
use App\Models\JenisSampel;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class JenisSampelResource extends Resource
{
    protected static ?string $model = JenisSampel::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-squares-plus';
    
    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Jenis Sampel';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('nama_sampel')
                    ->label('Nama Sampel')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('kode_sampel')
                    ->label('Kode Sampel')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_sampel')
                    ->label('Nama Sampel')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kode_sampel')
                    ->label('Kode Sampel')
                    ->searchable(),
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
            'index' => Pages\ManageJenisSampels::route('/'),
        ];
    }
}
