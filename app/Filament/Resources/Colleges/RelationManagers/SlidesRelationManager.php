<?php

namespace App\Filament\Resources\Colleges\RelationManagers;

use App\Filament\Forms\Components\CloudinaryImageUpload;
use App\Filament\Resources\Slides\SlideResource;
use App\Models\Slide;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SlidesRelationManager extends RelationManager
{
    protected static string $relationship = 'slides';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->rows(3),

                CloudinaryImageUpload::make('image')
                    ->label('Image')
                    ->directory('eduhub/slides'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn (Slide $record): string => SlideResource::getUrl('edit', ['record' => $record]))
            ->recordTitleAttribute('title')
            ->columns([
                ImageColumn::make('image')
                    ->label('Image'),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Slide $record): string => SlideResource::getUrl('edit', ['record' => $record]))
                    ->openUrlInNewTab(false),

                TextColumn::make('description')
                    ->limit(50)
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
