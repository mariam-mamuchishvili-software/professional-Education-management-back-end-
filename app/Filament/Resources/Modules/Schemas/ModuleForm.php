<?php

namespace App\Filament\Resources\Modules\Schemas;

use App\Models\Student;
use App\Models\Teacher;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ModuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Module Details')
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
                                    ->label('Duration (hours)')
                                    ->integer()
                                    ->minValue(1)
                                    ->required(),

                                TextInput::make('credits')
                                    ->integer()
                                    ->minValue(1)
                                    ->required(),

                                Textarea::make('description')
                                    ->maxLength(65535)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Relationships')
                    ->schema([
                        Select::make('teachers')
                            ->relationship('teachers', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn (Teacher $record): string => $record->full_name)
                            ->multiple()
                            ->preload()
                            ->searchable(['first_name', 'last_name', 'email']),

                        Select::make('professions')
                            ->relationship('professions', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),

                        Select::make('students')
                            ->relationship('students', 'first_name')
                            ->getOptionLabelFromRecordUsing(fn (Student $record): string => $record->full_name)
                            ->multiple()
                            ->preload()
                            ->searchable(['first_name', 'last_name', 'email']),
                    ]),
            ]);
    }
}
