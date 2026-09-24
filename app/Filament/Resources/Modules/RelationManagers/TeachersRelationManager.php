<?php

namespace App\Filament\Resources\Modules\RelationManagers;

use App\Filament\Resources\Teachers\TeacherResource;
use App\Models\Teacher;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeachersRelationManager extends RelationManager
{
    protected static string $relationship = 'teachers';

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Teacher $record): string => TeacherResource::getUrl('edit', ['record' => $record]))
            ->recordTitleAttribute('first_name')
            ->columns([
                TextColumn::make('first_name')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Teacher $record): string => TeacherResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(false),

                TextColumn::make('last_name')
                    ->searchable(),

                TextColumn::make('specialization')
                    ->searchable(),
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
