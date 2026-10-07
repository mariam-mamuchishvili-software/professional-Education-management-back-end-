<?php

namespace App\Filament\Teacher\Resources\Groups\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GroupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Group Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('code'),
                        TextEntry::make('profession.name')
                            ->label('Profession'),
                        TextEntry::make('capacity')
                            ->numeric(),
                        TextEntry::make('study_shift')
                            ->badge(),
                    ]),
            ]);
    }
}
