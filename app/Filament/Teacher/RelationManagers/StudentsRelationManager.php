<?php

namespace App\Filament\Teacher\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Read-only list of the students of one of the signed-in teacher's modules or groups.
 * The owner record is already limited to the teacher by its resource's query, so the
 * admin-only StudentPolicy is not consulted.
 */
class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static bool $shouldSkipAuthorization = true;

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('first_name')
            ->columns([
                TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),

                TextColumn::make('phone'),
            ])
            ->defaultSort('last_name');
    }
}
