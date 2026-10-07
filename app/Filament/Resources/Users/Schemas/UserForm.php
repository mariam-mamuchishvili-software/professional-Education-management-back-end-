<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
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
                                    ->minLength(8)
                                    ->maxLength(255)
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn (?string $state): bool => filled($state))
                                    ->helperText(fn (string $operation): ?string => $operation === 'edit' ? 'Leave empty to keep the current password.' : null)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Section::make('Access')
                    ->schema([
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record): string => self::roleLabel($record->name))
                            ->multiple()
                            ->preload()
                            ->disabled(fn (?User $record): bool => $record?->is(auth()->user()) ?? false)
                            ->helperText('Users without a role cannot sign in to the admin panel. You cannot change your own roles.'),
                    ]),
            ]);
    }

    private static function roleLabel(string $name): string
    {
        return match (UserRole::tryFrom($name)) {
            UserRole::SuperAdmin => 'Super admin',
            UserRole::CollegeAdmin => 'College admin',
            null => $name,
        };
    }
}
