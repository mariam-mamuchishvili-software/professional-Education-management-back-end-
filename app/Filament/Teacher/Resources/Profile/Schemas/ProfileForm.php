<?php

namespace App\Filament\Teacher\Resources\Profile\Schemas;

use App\Filament\Forms\Components\CloudinaryImageUpload;
use App\Models\SocialLink;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal Details')
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
                                    ->maxLength(20),

                                TextInput::make('specialization')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                CloudinaryImageUpload::make('image')
                                    ->label('Profile Image')
                                    ->directory('eduhub/teachers')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Change Password')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('password')
                                    ->label('New password')
                                    ->password()
                                    ->revealable()
                                    ->minLength(8)
                                    ->maxLength(255)
                                    ->confirmed()
                                    ->dehydrated(fn (?string $state): bool => filled($state))
                                    ->helperText('Leave empty to keep the current password.'),

                                TextInput::make('password_confirmation')
                                    ->label('Confirm new password')
                                    ->password()
                                    ->revealable()
                                    ->requiredWith('password')
                                    ->dehydrated(false),
                            ]),
                    ]),

                Section::make('Profile & Social Links')
                    ->description('Shown on your public teacher details page.')
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
            ]);
    }
}
