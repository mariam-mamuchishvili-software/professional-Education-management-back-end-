<?php

namespace App\Filament\Resources\Trainings\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TrainingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Training Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                ImageEntry::make('poster')
                                    ->label('Poster')
                                    ->columnSpanFull(),

                                TextEntry::make('title')
                                    ->columnSpanFull(),

                                TextEntry::make('video_link')
                                    ->label('Video link')
                                    ->url(fn (?string $state): ?string => $state, shouldOpenInNewTab: true)
                                    ->placeholder('—')
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
