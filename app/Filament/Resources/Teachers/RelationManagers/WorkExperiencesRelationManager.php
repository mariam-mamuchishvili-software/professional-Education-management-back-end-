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
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkExperiencesRelationManager extends RelationManager
{
    protected static string $relationship = 'workExperiences';

    protected static ?string $title = 'Work Experience';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('organization')
                    ->required()
                    ->maxLength(255),

                TextInput::make('position')
                    ->required()
                    ->maxLength(255),

                DatePicker::make('start_date')
                    ->required(),

                DatePicker::make('end_date')
                    ->afterOrEqual('start_date')
                    ->hidden(fn (Get $get): bool => (bool) $get('is_current')),

                Toggle::make('is_current')
                    ->label('Currently works here')
                    ->live()
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
            ->recordTitleAttribute('position')
            ->defaultSort('start_date', 'desc')
            ->columns([
                TextColumn::make('organization')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('position')
                    ->searchable(),

                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->date()
                    ->placeholder('—'),

                IconColumn::make('is_current')
                    ->label('Current')
                    ->boolean(),
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
