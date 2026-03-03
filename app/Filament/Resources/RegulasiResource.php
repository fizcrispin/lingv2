<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegulasiResource\Pages;
use App\Models\Regulasi;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class RegulasiResource extends Resource
{
    protected static ?string $model = Regulasi::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-book-open';
    
    protected static string|\UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Regulasi';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextArea::make('nama_regulasi')
                    ->label('Nama Regulasi')
                    ->required()
                    ->columnSpanFull()
                    ->maxLength(10000),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_regulasi')
                    ->label('Nama Regulasi')
                    ->searchable()
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
            'index' => Pages\ManageRegulasis::route('/'),
        ];
    }
}
