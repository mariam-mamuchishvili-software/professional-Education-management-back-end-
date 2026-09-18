<?php

namespace App\Filament\Resources\Colleges\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CollegeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('College Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                ImageEntry::make('poster')
                                    ->label('Poster')
                                    ->circular()
                                    ->columnSpanFull(),

                                TextEntry::make('name')
                                    ->columnSpanFull(),

                                TextEntry::make('address')
                                    ->columnSpanFull(),

                                TextEntry::make('email')
                                    ->label('Email address')
                                    ->copyable()
                                    ->icon('heroicon-o-envelope'),

                                TextEntry::make('phone')
                                    ->icon('heroicon-o-phone'),

                                TextEntry::make('website')
                                    ->url(fn (?string $state): ?string => $state)
                                    ->openUrlInNewTab()
                                    ->placeholder('—')
                                    ->columnSpanFull(),

                                TextEntry::make('teachers_count')
                                    ->label('Teachers')
                                    ->state(fn ($record) => $record->teachers()->count()),

                                TextEntry::make('created_at')
                                    ->dateTime(),

                                TextEntry::make('updated_at')
                                    ->dateTime(),
                            ]),
                    ]),
            ]);
    }
}
