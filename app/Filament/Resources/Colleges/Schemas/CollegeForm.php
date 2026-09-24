<?php

namespace App\Filament\Resources\Colleges\Schemas;

use App\Filament\Forms\Components\CloudinaryImageUpload;
use App\Models\SocialLink;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                                    ->maxLength(20),

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

                                Select::make('professions')
                                    ->relationship('professions', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->columnSpanFull(),

                                CloudinaryImageUpload::make('poster')
                                    ->label('Poster')
                                    ->directory('eduhub/colleges')
                                    ->columnSpanFull(),

                                CloudinaryImageUpload::make('logo')
                                    ->label('Logo')
                                    ->directory('eduhub/colleges')
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
