<?php

namespace App\Filament\Teacher\Resources\Profile\Pages;

use App\Filament\Teacher\Resources\Profile\ProfileResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditProfile extends EditRecord
{
    protected static string $resource = ProfileResource::class;

    protected static ?string $title = 'My profile';

    /**
     * The page has no record in its URL: it always edits the signed-in teacher.
     */
    public function mount(int|string|null $record = null): void
    {
        parent::mount(Filament::auth()->id());
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
