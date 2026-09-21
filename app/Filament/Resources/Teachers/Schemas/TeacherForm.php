<?php

namespace App\Filament\Resources\Teachers\Schemas;

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

class TeacherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Teacher Details')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('first_name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('last_name')
                                    ->required()
                                    ->maxLength(255),

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

                                TextInput::make('specialization')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                FileUpload::make('image')
                                    ->label('Profile Image')
                                    ->image()
                                    ->maxSize(4096)
                                    ->imagePreviewHeight(150)
                                    ->fetchFileInformation(false)
                                    ->saveUploadedFileUsing(fn (TemporaryUploadedFile $file): string => app(CloudinaryUploader::class)
                                        ->upload($file, 'eduhub/teachers'))
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
                    ->description('Extended profile shown on the teacher details page.')
                    ->relationship('detail')
                    ->schema([
                        Textarea::make('biography')
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

                Section::make('Relationships')
                    ->schema([
                        Select::make('colleges')
                            ->relationship('colleges', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),

                        Select::make('modules')
                            ->relationship('modules', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ]),
            ]);
    }
}
