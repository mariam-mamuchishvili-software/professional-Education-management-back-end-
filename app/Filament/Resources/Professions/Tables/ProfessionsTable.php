<?php

namespace App\Filament\Resources\Professions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProfessionsTable
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

                TextColumn::make('qualification')
                    ->badge()
                    ->sortable(),

                TextColumn::make('duration')
                    ->label('Duration (years)')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('modules.name')
                    ->label('Modules')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->placeholder('—'),

                TextColumn::make('groups.name')
                    ->label('Groups')
                    ->badge()
                    ->color('warning')
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->placeholder('—'),

                TextColumn::make('colleges.name')
                    ->label('Colleges')
                    ->badge()
                    ->color('primary')
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('qualification')
                    ->options([
                        'Certificate' => 'Certificate',
                        'Diploma' => 'Diploma',
                        'Bachelor' => 'Bachelor',
                        'Master' => 'Master',
                    ]),

                SelectFilter::make('modules')
                    ->relationship('modules', 'name')
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
