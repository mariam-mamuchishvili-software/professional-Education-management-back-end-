<?php

namespace App\Filament\Resources\Slides\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SlideInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Slide Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                ImageEntry::make('image')
                                    ->label('Image')
                                    ->columnSpanFull(),

                                TextEntry::make('college.name')
                                    ->label('College')
                                    ->columnSpanFull(),

                                TextEntry::make('title')
                                    ->columnSpanFull(),

                                TextEntry::make('description')
                                    ->placeholder('—')
                                    ->columnSpanFull(),

                                TextEntry::make('created_at')
                                    ->dateTime(),

                                TextEntry::make('updated_at')
                                    ->dateTime(),
                            ]),
                    ]),
            ]);
    }
}
