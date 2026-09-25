<?php

namespace App\Filament\Resources\Trainings\Schemas;

use App\Filament\Forms\Components\CloudinaryImageUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TrainingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Training Details')
                    ->description('A training with an optional poster and video.')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('video_link')
                            ->label('Video link')
                            ->url()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                        CloudinaryImageUpload::make('poster')
                            ->label('Poster')
                            ->directory('eduhub/trainings')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
