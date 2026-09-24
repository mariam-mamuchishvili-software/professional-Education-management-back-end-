<?php

namespace App\Filament\Resources\Colleges\RelationManagers;

use App\Filament\Forms\Components\CloudinaryImageUpload;
use App\Filament\Resources\Teachers\TeacherResource;
use App\Models\Teacher;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeachersRelationManager extends RelationManager
{
    protected static string $relationship = 'teachers';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('last_name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(20),

                TextInput::make('specialization')
                    ->required()
                    ->maxLength(255),

                CloudinaryImageUpload::make('image')
                    ->label('Profile Image')
                    ->directory('eduhub/teachers')
                    ->columnSpanFull(),
            ]);
    }

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

                TextColumn::make('email')
                    ->searchable(),

                TextColumn::make('specialization')
                    ->searchable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                EditAction::make(),
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
