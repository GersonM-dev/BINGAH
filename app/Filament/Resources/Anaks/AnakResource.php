<?php

namespace App\Filament\Resources\Anaks;

use App\Models\Anak;
use App\Filament\Resources\Anaks\Pages\CreateAnak;
use App\Filament\Resources\Anaks\Pages\EditAnak;
use App\Filament\Resources\Anaks\Pages\ListAnaks;
use App\Filament\Resources\Anaks\Pages\ViewAnak;
use App\Filament\Resources\Anaks\Schemas\AnakForm;
use App\Filament\Resources\Anaks\Schemas\AnakInfolist;
use App\Filament\Resources\Anaks\Tables\AnaksTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AnakResource extends Resource
{
    protected static ?string $model = Anak::class;

    protected static ?string $pluralLabel = "Data Anak";
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return AnakForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AnakInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AnaksTable::configure($table);
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
            'index' => ListAnaks::route('/'),
            'create' => CreateAnak::route('/create'),
            'view' => ViewAnak::route('/{record}'),
            'edit' => EditAnak::route('/{record}/edit'),
        ];
    }
}
