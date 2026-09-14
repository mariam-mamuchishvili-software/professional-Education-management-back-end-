<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Resolve which relations to eager load from the request's "include" query parameter.
     *
     * $allowed is a tree describing, per model, which relation paths may be requested.
     * Each key is an allowed relation name; its value is the tree of relations allowed
     * to be nested under it (an empty array means no further nesting is allowed there).
     * The tree's own depth caps how many levels deep an include may go, so it should
     * never be defined more than 3 levels deep.
     *
     * Any path segment not present in the tree at its level stops resolution for that
     * path: the valid prefix is kept and the rest of the path is dropped, so a bad or
     * over-deep include is silently ignored instead of erroring.
     *
     * @param  array<string, array<mixed>>  $allowed
     * @return array<int, string>
     */
    protected function resolveIncludes(Request $request, array $allowed): array
    {
        $include = $request->query('include', '');
        $include = is_array($include) ? implode(',', $include) : (string) $include;

        $requested = array_filter(array_map('trim', explode(',', $include)));

        $resolved = [];

        foreach ($requested as $path) {
            $valid = $this->resolveIncludePath(explode('.', $path), $allowed);

            if ($valid !== null) {
                $resolved[] = $valid;
            }
        }

        return array_values(array_unique($resolved));
    }

    /**
     * Walk a single dot-separated include path against the allowlist tree, keeping only
     * the valid leading segments.
     *
     * @param  array<int, string>  $segments
     * @param  array<string, array<mixed>>  $allowedLevel
     */
    private function resolveIncludePath(array $segments, array $allowedLevel): ?string
    {
        $validSegments = [];

        foreach ($segments as $segment) {
            $segment = trim($segment);

            if ($segment === '' || ! array_key_exists($segment, $allowedLevel)) {
                break;
            }

            $validSegments[] = $segment;
            $allowedLevel = $allowedLevel[$segment];
        }

        return $validSegments === [] ? null : implode('.', $validSegments);
    }
}
