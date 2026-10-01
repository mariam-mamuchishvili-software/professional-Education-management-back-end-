<?php

namespace App\Filament\Resources\Groups\RelationManagers;

use App\Filament\Resources\Students\StudentResource;
use App\Filament\Support\AuthorizesAttachAndDetachWithPolicy;
use App\Models\Student;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class StudentsRelationManager extends RelationManager
{
    use AuthorizesAttachAndDetachWithPolicy;

    protected static string $relationship = 'students';

    /**
     * Anyone allowed to view the owner record may see its students, including teachers
     * who cannot open the global student list. Attaching and detaching are checked against
     * the StudentPolicy, so teachers get a read-only list.
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('view', $ownerRecord) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Student $record): ?string => StudentResource::canEdit($record) ? StudentResource::getUrl('edit', ['record' => $record]) : null)
            ->recordTitleAttribute('first_name')
            ->columns([
                TextColumn::make('first_name')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Student $record): ?string => StudentResource::canEdit($record) ? StudentResource::getUrl('edit', ['record' => $record]) : null)
                    ->openUrlInNewTab(false),

                TextColumn::make('last_name')
                    ->searchable(),

                TextColumn::make('email')
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
