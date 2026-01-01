<?php

namespace App\Filament\Resources\PendaftarLingkungans;

use App\Filament\Resources\PendaftarLingkungans\Pages\CreatePendaftarLingkungan;
use App\Filament\Resources\PendaftarLingkungans\Pages\EditPendaftarLingkungan;
use App\Filament\Resources\PendaftarLingkungans\Pages\ListPendaftarLingkungans;
use App\Filament\Resources\PendaftarLingkungans\Schemas\PendaftarLingkunganForm;
use App\Filament\Resources\PendaftarLingkungans\Tables\PendaftarLingkungansTable;
use App\Models\PendaftarLingkungan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Filament\Resources\PendaftarLingkunganResource\Pages;
use App\Filament\Resources\PendaftarLingkunganResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\ReplicateAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextInputColumn;
use Carbon\Carbon;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Group;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use App\Models\ParameterLingkungan;
use Filament\Forms\Components\Toggle;
use App\Models\PaketParameter;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Fieldset;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkAction;


class PendaftarLingkunganResource extends Resource
{
    protected static ?string $model = PendaftarLingkungan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'PendaftarLingkungan';

    public static function form(Schema $schema): Schema
    {
        return PendaftarLingkunganForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PendaftarLingkungansTable::configure($table);
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
            'index' => ListPendaftarLingkungans::route('/'),
            // 'create' => CreatePendaftarLingkungan::route('/create'),
            // 'edit' => EditPendaftarLingkungan::route('/{record}/edit'),
        ];
    }
}
