<?php

namespace App\Filament\Resources\Students\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Photo')
                    ->circular(),

                TextColumn::make('first_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('last_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->searchable(),

                TextColumn::make('birth_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('groups.name')
                    ->label('Groups')
                    ->badge()
                    ->separator(',')
                    ->placeholder('—'),

                TextColumn::make('modules.name')
                    ->label('Modules')
                    ->badge()
                    ->separator(',')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('groups')
                    ->relationship('groups', 'name')
                    ->multiple()
                    ->preload(),

                SelectFilter::make('modules')
                    ->relationship('modules', 'name')
                    ->multiple()
                    ->preload(),

                Filter::make('birth_date')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('birth_date_from'),
                                DatePicker::make('birth_date_until'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['birth_date_from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('birth_date', '>=', $date),
                            )
                            ->when(
                                $data['birth_date_until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('birth_date', '<=', $date),
                            );
                    }),
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
            ->defaultSort('last_name');
    }
}
