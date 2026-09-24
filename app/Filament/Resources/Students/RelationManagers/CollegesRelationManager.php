<?php

namespace App\Filament\Resources\Students\RelationManagers;

use App\Filament\Resources\Colleges\CollegeResource;
use App\Models\College;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CollegesRelationManager extends RelationManager
{
    protected static string $relationship = 'colleges';

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (College $record): string => CollegeResource::getUrl('edit', ['record' => $record]))
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->url(fn (College $record): string => CollegeResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(false),

                TextColumn::make('email')
                    ->searchable(),

                TextColumn::make('phone'),
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
