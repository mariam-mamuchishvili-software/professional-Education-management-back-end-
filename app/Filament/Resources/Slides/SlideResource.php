<?php

namespace App\Filament\Resources\Slides;

use App\Filament\Resources\Slides\Pages\CreateSlide;
use App\Filament\Resources\Slides\Pages\EditSlide;
use App\Filament\Resources\Slides\Pages\ListSlides;
use App\Filament\Resources\Slides\Pages\ViewSlide;
use App\Filament\Resources\Slides\Schemas\SlideForm;
use App\Filament\Resources\Slides\Schemas\SlideInfolist;
use App\Filament\Resources\Slides\Tables\SlidesTable;
use App\Models\Slide;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SlideResource extends Resource
{
    protected static ?string $model = Slide::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Education Management';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return SlideForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SlideInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SlidesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSlides::route('/'),
            'create' => CreateSlide::route('/create'),
            'view' => ViewSlide::route('/{record}'),
            'edit' => EditSlide::route('/{record}/edit'),
        ];
    }
}
