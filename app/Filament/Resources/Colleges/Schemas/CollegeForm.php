<?php

namespace App\Filament\Resources\Colleges\Schemas;

use App\Models\SocialLink;
use App\Services\Cloudinary\CloudinaryUploader;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CollegeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('College Details')
                    ->description('Basic information about the college.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                TextInput::make('address')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                TextInput::make('email')
                                    ->label('Email address')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),

                                TextInput::make('phone')
                                    ->tel()
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('website')
                                    ->url()
                                    ->maxLength(255)
                                    ->nullable()
                                    ->columnSpanFull(),

                                TextInput::make('latitude')
                                    ->numeric()
                                    ->minValue(-90)
                                    ->maxValue(90)
                                    ->nullable(),

                                TextInput::make('longitude')
                                    ->numeric()
                                    ->minValue(-180)
                                    ->maxValue(180)
                                    ->nullable(),

                                FileUpload::make('poster')
                                    ->label('Poster')
                                    ->image()
                                    ->maxSize(4096)
                                    ->imagePreviewHeight(150)
                                    ->fetchFileInformation(false)
                                    ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(CloudinaryUploader::class)
                                        ->upload($file, 'eduhub/colleges'))
                                    ->getUploadedFileUsing(fn (string $file): array => [
                                        'name' => basename(parse_url($file, PHP_URL_PATH) ?: $file),
                                        'size' => 0,
                                        'type' => 'image',
                                        'url' => $file,
                                    ])
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Profile & Social Links')
                    ->description('Extended profile shown on the college details page.')
                    ->relationship('detail')
                    ->schema([
                        Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull(),

                        Textarea::make('additional_information')
                            ->rows(3)
                            ->columnSpanFull(),

                        Repeater::make('socialLinks')
                            ->relationship('socialLinks')
                            ->label('Social links')
                            ->schema([
                                Select::make('platform')
                                    ->options(SocialLink::PLATFORMS)
                                    ->required(),

                                TextInput::make('url')
                                    ->url()
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Add social link')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
