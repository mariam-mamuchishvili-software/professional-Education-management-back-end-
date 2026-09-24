<?php

namespace App\Filament\Resources\Colleges\RelationManagers;

use App\Filament\Resources\Professions\ProfessionResource;
use App\Models\Profession;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProfessionsRelationManager extends RelationManager
{
    protected static string $relationship = 'professions';

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Profession $record): string => ProfessionResource::getUrl('edit', ['record' => $record]))
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Profession $record): string => ProfessionResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(false),

                TextColumn::make('code')
                    ->searchable(),

                TextColumn::make('qualification')
                    ->badge(),
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
