<?php

namespace App\Filament\Widgets;

use App\Models\Module;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestModules extends TableWidget
{
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Latest Modules')
            ->query(fn (): Builder => Module::query()->latest()->limit(5))
            ->paginated(false)
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('code')
                    ->badge(),
                TextColumn::make('credits'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Added'),
            ]);
    }
}
