<?php

namespace App\Filament\Resources\Professions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProfessionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profession Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name'),
                                TextEntry::make('code')
                                    ->badge(),

                                TextEntry::make('duration')
                                    ->label('Duration (years)')
                                    ->suffix(' year(s)'),

                                TextEntry::make('qualification')
                                    ->badge(),

                                TextEntry::make('description')
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Relationships')
                    ->schema([
                        TextEntry::make('modules.name')
                            ->label('Modules')
                            ->badge()
                            ->placeholder('—'),

                        TextEntry::make('groups_count')
                            ->label('Groups')
                            ->state(fn ($record) => $record->groups()->count()),
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
