<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Resolve which relations to eager load from the request's "include" query parameter,
     * limited to the given whitelist of allowed relation names.
     *
     * @param  array<int, string>  $allowed
     * @return array<int, string>
     */
    protected function resolveIncludes(Request $request, array $allowed): array
    {
        $include = $request->query('include', '');
        $include = is_array($include) ? implode(',', $include) : (string) $include;

        $requested = array_filter(array_map('trim', explode(',', $include)));

        return array_values(array_intersect($allowed, $requested));
    }
}
