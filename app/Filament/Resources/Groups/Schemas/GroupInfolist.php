<?php

namespace App\Filament\Resources\Groups\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GroupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Group Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('profession.name')
                                    ->label('Profession')
                                    ->columnSpanFull(),

                                TextEntry::make('name'),
                                TextEntry::make('code')
                                    ->badge(),

                                TextEntry::make('capacity'),

                                TextEntry::make('study_shift')
                                    ->badge(),
                            ]),
                    ]),

                Section::make('Students')
                    ->schema([
                        TextEntry::make('students.first_name')
                            ->label('Students')
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
