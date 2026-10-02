<?php

namespace App\Filament\Resources\Teachers\RelationManagers;

use App\Filament\Forms\Components\CloudinaryImageUpload;
use App\Models\TeacherTraining;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrainingsRelationManager extends RelationManager
{
    protected static string $relationship = 'trainings';

    protected static ?string $title = 'Trainings & Certificates';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                TextInput::make('organizer')
                    ->required()
                    ->maxLength(255)
                    ->helperText('College, ministry, private training center or any other organizer.'),

                TextInput::make('certificate_number')
                    ->maxLength(255),

                DatePicker::make('issue_date'),

                DatePicker::make('expiry_date')
                    ->afterOrEqual('issue_date'),

                CloudinaryImageUpload::make('certificate_url')
                    ->label('Certificate (PDF or image)')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(5120)
                    ->directory('eduhub/teacher-certificates')
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->rows(3)
                    ->maxLength(5000)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->defaultSort('issue_date', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('organizer')
                    ->searchable(),

                TextColumn::make('issue_date')
                    ->date()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('expiry_date')
                    ->date()
                    ->placeholder('—'),

                TextColumn::make('certificate_url')
                    ->label('Certificate')
                    ->formatStateUsing(fn (): string => 'Open')
                    ->url(fn (TeacherTraining $record): ?string => $record->certificate_url)
                    ->openUrlInNewTab()
                    ->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
