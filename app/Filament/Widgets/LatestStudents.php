<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestStudents extends TableWidget
{
    protected static ?int $sort = 2;

    /**
     * Shows data across the whole institution, so it is limited to administrators.
     */
    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Latest Students')
            ->query(fn (): Builder => Student::query()->latest()->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('first_name'),
                TextColumn::make('last_name'),
                TextColumn::make('email'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Registered'),
            ]);
    }
}
