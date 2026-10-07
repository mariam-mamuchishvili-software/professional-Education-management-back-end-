<?php

namespace App\Filament\Resources\Teachers\Schemas;

use App\Filament\Forms\Components\CloudinaryImageUpload;
use App\Models\SocialLink;
use App\Services\Passwords\PassphraseGenerator;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

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

                                TextInput::make('password')
                                    ->password()
                                    ->revealable()
                                    ->copyable()
                                    ->default(fn (): string => app(PassphraseGenerator::class)->generate())
                                    ->suffixAction(
                                        Action::make('generatePassphrase')
                                            ->label('Generate new passphrase')
                                            ->icon(Heroicon::OutlinedArrowPath)
                                            ->action(fn (Set $set): mixed => $set('password', app(PassphraseGenerator::class)->generate())),
                                    )
                                    ->minLength(8)
                                    ->maxLength(255)
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (?string $state): bool => filled($state))
                                    ->helperText(fn (string $operation): string => $operation === 'create'
                                        ? 'A passphrase is generated automatically. Copy it and give it to the teacher.'
                                        : 'Leave empty to keep the current password, or generate a new passphrase.'),

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
