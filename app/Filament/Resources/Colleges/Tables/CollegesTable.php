<?php

namespace App\Filament\Resources\Colleges\Tables;

use App\Filament\Support\RelatedNamesTooltip;
use App\Models\College;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CollegesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'teachers' => fn (BelongsToMany $query) => $query->orderBy('teachers.last_name')->limit(5),
                'students' => fn (BelongsToMany $query) => $query->orderBy('students.last_name')->limit(5),
            ]))
            ->columns([
                ImageColumn::make('poster')
                    ->label('Poster')
                    ->circular(),

                ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('phone')
                    ->searchable(),

                TextColumn::make('website')
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('professions.name')
                    ->label('Professions')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('teachers_count')
                    ->label('Teachers')
                    ->counts('teachers')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->tooltip(fn (College $record): ?string => RelatedNamesTooltip::make($record->teachers->pluck('full_name'), $record->teachers_count)),

                TextColumn::make('students_count')
                    ->label('Students')
                    ->counts('students')
                    ->badge()
                    ->color('warning')
                    ->sortable()
                    ->tooltip(fn (College $record): ?string => RelatedNamesTooltip::make($record->students->pluck('full_name'), $record->students_count)),

                TextColumn::make('slides_count')
                    ->label('Slides')
                    ->counts('slides')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('has_teachers')
                    ->label('Has teachers')
                    ->query(fn (Builder $query): Builder => $query->has('teachers')),

                SelectFilter::make('professions')
                    ->relationship('professions', 'name')
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
