<?php

namespace App\Filament\Teacher\Resources\Groups\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->searchable(),

                TextColumn::make('profession.name')
                    ->label('Profession'),

                TextColumn::make('capacity')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('study_shift')
                    ->badge(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('name');
    }
}
