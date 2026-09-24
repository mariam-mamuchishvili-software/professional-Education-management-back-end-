<?php

namespace App\Filament\Resources\Groups\Tables;

use App\Filament\Resources\Professions\ProfessionResource;
use App\Filament\Support\RelatedNamesTooltip;
use App\Models\Group;
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

class GroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'students' => fn (BelongsToMany $query) => $query->orderBy('students.last_name')->limit(5),
            ]))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->searchable()
                    ->badge(),

                TextColumn::make('profession.name')
                    ->label('Profession')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Group $record): ?string => $record->profession_id
                        ? ProfessionResource::getUrl('edit', ['record' => $record->profession_id])
                        : null)
                    ->placeholder('—'),

                TextColumn::make('capacity')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('study_shift')
                    ->badge()
                    ->sortable(),

                TextColumn::make('students_count')
                    ->label('Students')
                    ->counts('students')
                    ->badge()
                    ->color('warning')
                    ->sortable()
                    ->tooltip(fn (Group $record): ?string => RelatedNamesTooltip::make($record->students->pluck('full_name'), $record->students_count)),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('profession')
                    ->relationship('profession', 'name')
                    ->preload(),

                SelectFilter::make('study_shift')
                    ->options([
                        'morning' => 'Morning',
                        'afternoon' => 'Afternoon',
                        'evening' => 'Evening',
                    ]),
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
