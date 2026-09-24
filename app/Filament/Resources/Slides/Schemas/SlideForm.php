<?php

namespace App\Filament\Resources\Slides\Schemas;

use App\Filament\Forms\Components\CloudinaryImageUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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

                        CloudinaryImageUpload::make('image')
                            ->label('Image')
                            ->directory('eduhub/slides')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
