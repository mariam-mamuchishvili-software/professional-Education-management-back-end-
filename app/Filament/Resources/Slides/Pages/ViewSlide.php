<?php

namespace App\Filament\Resources\Slides\Pages;

use App\Filament\Resources\Slides\SlideResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSlide extends ViewRecord
{
    protected static string $resource = SlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
