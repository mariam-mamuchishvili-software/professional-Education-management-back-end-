<?php

namespace App\Filament\Resources\Groups\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Group Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('profession_id')
                                    ->label('Profession')
                                    ->relationship('profession', 'name')
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->columnSpanFull(),

                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('code')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                TextInput::make('capacity')
                                    ->numeric()
                                    ->minValue(1)
                                    ->required(),

                                Select::make('study_shift')
                                    ->options([
                                        'morning' => 'Morning',
                                        'afternoon' => 'Afternoon',
                                        'evening' => 'Evening',
                                    ])
                                    ->required(),
                            ]),
                    ]),

                Section::make('Students')
                    ->schema([
                        Select::make('students')
                            ->relationship('students', 'first_name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ]),
            ]);
    }
}
