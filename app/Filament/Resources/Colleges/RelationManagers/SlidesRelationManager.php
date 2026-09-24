<?php

namespace App\Filament\Resources\Colleges\RelationManagers;

use App\Filament\Resources\Slides\SlideResource;
use App\Models\Slide;
use App\Services\Cloudinary\CloudinaryUploader;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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

                FileUpload::make('image')
                    ->label('Image')
                    ->image()
                    ->maxSize(4096)
                    ->imagePreviewHeight(150)
                    ->fetchFileInformation(false)
                    ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(CloudinaryUploader::class)
                        ->upload($file, 'eduhub/slides'))
                    ->getUploadedFileUsing(fn (string $file): array => [
                        'name' => basename(parse_url($file, PHP_URL_PATH) ?: $file),
                        'size' => 0,
                        'type' => 'image',
                        'url' => $file,
                    ]),
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
