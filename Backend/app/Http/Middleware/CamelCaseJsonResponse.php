<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CamelCaseJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response instanceof JsonResponse) {
            $response->setData($this->camelize($response->getData(true)));
        }

        return $response;
    }

    private function camelize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $result = [];

        foreach ($value as $key => $item) {
            $newKey = is_string($key) ? Str::camel($key) : $key;
            $result[$newKey] = $this->camelize($item);
        }

        return $result;
    }
}
