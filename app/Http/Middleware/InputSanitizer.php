<?php

namespace App\Http\Middleware;

use Closure;

class InputSanitizer
{
    public function handle($request, Closure $next)
    {
        // Sanitize all input fields (basic example)
        $input = $request->all();
        array_walk_recursive($input, function (&$value) {
            $value = strip_tags($value);
        });
        $request->merge($input);
        return $next($request);
    }
}