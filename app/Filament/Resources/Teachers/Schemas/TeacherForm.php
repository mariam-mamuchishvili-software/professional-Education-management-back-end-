<?php

namespace App\Filament\Resources\Teachers\Schemas;

use App\Filament\Forms\Components\CloudinaryImageUpload;
use App\Models\SocialLink;
use App\Models\Teacher;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

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

                Section::make('Panel Access')
                    ->description('Link a user account so this teacher can sign in and see their own modules, colleges and groups.')
                    ->schema([
                        Select::make('user_id')
                            ->label('User account')
                            ->relationship(
                                'user',
                                'email',
                                modifyQueryUsing: fn (Builder $query, ?Teacher $record): Builder => $query->whereDoesntHave(
                                    'teacher',
                                    fn (Builder $query) => $query->whereKeyNot($record?->getKey()),
                                ),
                            )
                            ->searchable()
                            ->preload()
                            ->unique(ignoreRecord: true)
                            ->helperText('Only users not linked to another teacher are listed. The user also needs the Teacher role.'),
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
