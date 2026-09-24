<?php

namespace App\Filament\Resources\Students\RelationManagers;

use App\Filament\Resources\Groups\GroupResource;
use App\Filament\Resources\Professions\ProfessionResource;
use App\Models\Group;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'groups';

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Group $record): string => GroupResource::getUrl('edit', ['record' => $record]))
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Group $record): string => GroupResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(false),

                TextColumn::make('code')
                    ->searchable(),

                TextColumn::make('profession.name')
                    ->label('Profession')
                    ->url(fn (Group $record): ?string => $record->profession_id
                        ? ProfessionResource::getUrl('edit', ['record' => $record->profession_id])
                        : null)
                    ->placeholder('—'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
