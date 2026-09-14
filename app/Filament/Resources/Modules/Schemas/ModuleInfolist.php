<?php

namespace App\Filament\Resources\Modules\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ModuleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Module Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('code')
                                    ->badge(),

                                TextEntry::make('duration')
                                    ->label('Duration (hours)'),

                                TextEntry::make('credits'),

                                TextEntry::make('description')
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Relationships')
                    ->schema([
                        TextEntry::make('teachers.first_name')
                            ->label('Teachers')
                            ->badge()
                            ->placeholder('—'),

                        TextEntry::make('professions.name')
                            ->label('Professions')
                            ->badge()
                            ->placeholder('—'),

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
