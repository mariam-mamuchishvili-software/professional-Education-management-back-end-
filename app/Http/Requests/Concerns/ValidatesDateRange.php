<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Builds the "ends on or after it starts" rule for form requests whose start date may be
 * missing (it is optional, or omitted from a partial update). Laravel's after_or_equal
 * fails outright when the referenced field is absent, so the start date is resolved from
 * the request or, failing that, from the record being updated, and the rule is skipped
 * when there is no usable start date to compare against.
 */
trait ValidatesDateRange
{
    protected function onOrAfterStartDate(string $startAttribute, ?Model $record = null): ?string
    {
        $startDate = $this->exists($startAttribute)
            ? $this->input($startAttribute)
            : $record?->getAttribute($startAttribute)?->toDateString();

        if (! is_string($startDate) || strtotime($startDate) === false) {
            return null;
        }

        return 'after_or_equal:'.$startDate;
    }
}
