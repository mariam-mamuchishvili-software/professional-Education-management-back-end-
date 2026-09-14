<?php

namespace App\Filament\Resources\Teachers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeacherInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Teacher Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('first_name'),
                                TextEntry::make('last_name'),

                                TextEntry::make('email')
                                    ->label('Email address')
                                    ->copyable()
                                    ->icon('heroicon-o-envelope'),

                                TextEntry::make('phone')
                                    ->icon('heroicon-o-phone'),

                                TextEntry::make('specialization')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Relationships')
                    ->schema([
                        TextEntry::make('colleges.name')
                            ->label('Colleges')
                            ->badge()
                            ->placeholder('—'),

                        TextEntry::make('modules.name')
                            ->label('Modules')
                            ->badge()
                            ->placeholder('—'),
                    ]),

                Section::make('Metadata')
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
