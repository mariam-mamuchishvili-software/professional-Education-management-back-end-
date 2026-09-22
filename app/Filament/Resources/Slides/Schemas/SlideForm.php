<?php

namespace App\Filament\Resources\Slides\Schemas;

use App\Services\Cloudinary\CloudinaryUploader;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Slide Details')
                    ->description('A carousel slide shown for a college.')
                    ->schema([
                        Select::make('college_id')
                            ->label('College')
                            ->relationship('college', 'name')
                            ->required()
                            ->preload()
                            ->searchable(),

                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

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
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
