<?php

namespace App\Filament\Resources\Modules\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ModulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->searchable()
                    ->badge(),

                TextColumn::make('duration')
                    ->label('Duration (hours)')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('credits')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('teachers_count')
                    ->label('Teachers')
                    ->counts('teachers')
                    ->badge(),

                TextColumn::make('professions_count')
                    ->label('Professions')
                    ->counts('professions')
                    ->badge(),

                TextColumn::make('students_count')
                    ->label('Students')
                    ->counts('students')
                    ->badge(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('professions')
                    ->relationship('professions', 'name')
                    ->multiple()
                    ->preload(),

                SelectFilter::make('teachers')
                    ->relationship('teachers', 'first_name')
                    ->multiple()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}
