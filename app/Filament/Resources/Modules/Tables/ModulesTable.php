<?php

namespace App\Filament\Resources\Modules\Tables;

use App\Filament\Support\RelatedNamesTooltip;
use App\Models\Module;
use App\Models\Teacher;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ModulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'teachers' => fn (BelongsToMany $query) => $query->orderBy('teachers.last_name')->limit(5),
                'students' => fn (BelongsToMany $query) => $query->orderBy('students.last_name')->limit(5),
            ]))
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

                TextColumn::make('professions.name')
                    ->label('Professions')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->placeholder('—'),

                TextColumn::make('teachers_count')
                    ->label('Teachers')
                    ->counts('teachers')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->tooltip(fn (Module $record): ?string => RelatedNamesTooltip::make($record->teachers->pluck('full_name'), $record->teachers_count)),

                TextColumn::make('students_count')
                    ->label('Students')
                    ->counts('students')
                    ->badge()
                    ->color('warning')
                    ->sortable()
                    ->tooltip(fn (Module $record): ?string => RelatedNamesTooltip::make($record->students->pluck('full_name'), $record->students_count)),

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
                    ->getOptionLabelFromRecordUsing(fn (Teacher $record): string => $record->full_name)
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
