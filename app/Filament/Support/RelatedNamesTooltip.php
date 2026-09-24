<?php

namespace App\Filament\Support;

use Illuminate\Support\Collection;

/**
 * Builds the hover tooltip for "*_count" badge columns: a comma-separated preview
 * of the first eager-loaded related names, with an ellipsis when more exist.
 * Callers must eager load a limited relation (e.g. ->limit(5)) to avoid N+1 queries.
 */
class RelatedNamesTooltip
{
    /**
     * @param  Collection<int, string>  $names
     */
    public static function make(Collection $names, ?int $total = null): ?string
    {
        if ($names->isEmpty()) {
            return null;
        }

        return $names->implode(', ').(($total ?? 0) > $names->count() ? ', …' : '');
    }
}
