<?php

namespace App\Filament\Resources\Professions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProfessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profession Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('code')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                TextInput::make('duration')
                                    ->label('Duration (years)')
                                    ->integer()
                                    ->minValue(1)
                                    ->required(),

                                Select::make('qualification')
                                    ->options([
                                        'Certificate' => 'Certificate',
                                        'Diploma' => 'Diploma',
                                        'Bachelor' => 'Bachelor',
                                        'Master' => 'Master',
                                    ])
                                    ->required(),

                                Textarea::make('description')
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Modules')
                    ->schema([
                        Select::make('modules')
                            ->relationship('modules', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ]),
            ]);
    }
}
