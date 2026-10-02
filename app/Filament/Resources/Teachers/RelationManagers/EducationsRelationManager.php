<?php

namespace App\Filament\Resources\Teachers\RelationManagers;

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

class EducationsRelationManager extends RelationManager
{
    protected static string $relationship = 'educations';

    protected static ?string $title = 'Education & Qualifications';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('institution')
                    ->required()
                    ->maxLength(255),

                TextInput::make('degree')
                    ->label('Degree / qualification')
                    ->required()
                    ->maxLength(255),

                TextInput::make('specialization')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                DatePicker::make('start_date'),

                DatePicker::make('end_date')
                    ->afterOrEqual('start_date'),

                Textarea::make('description')
                    ->rows(3)
                    ->maxLength(5000)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('degree')
            ->defaultSort('start_date', 'desc')
            ->columns([
                TextColumn::make('institution')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('degree')
                    ->searchable(),

                TextColumn::make('specialization')
                    ->searchable(),

                TextColumn::make('start_date')
                    ->date()
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('end_date')
                    ->date()
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
