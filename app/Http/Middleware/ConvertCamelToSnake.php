<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ConvertCamelToSnake
{
    public function handle(Request $request, Closure $next): Response
    {
        // Intercept input parameters and normalize them recursively
        $request->replace($this->snakeKeys($request->all()));

        return $next($request);
    }

    private function snakeKeys(array $array): array
    {
        return Arr::mapWithKeys($array, function ($value, $key) {
            if (is_array($value)) {
                $value = $this->snakeKeys($value);
            }
            return [Str::snake($key) => $value];
        });
    }
}
