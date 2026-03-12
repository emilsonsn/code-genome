<?php

namespace App\Http\Middleware;

use App\Infrastructure\Metrics\AccessMetricsRecorder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAccessMetrics
{
    public function __construct(private AccessMetricsRecorder $recorder) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldSkip($request)) {
            return $response;
        }

        $route = $request->route();

        if ($route === null) {
            return $response;
        }

        $this->recorder->record(
            method: $request->method(),
            path: '/'.ltrim($request->path(), '/'),
            routeName: $route->getName(),
            ipAddress: $request->ip()
        );

        return $response;
    }

    private function shouldSkip(Request $request): bool
    {
        if ($request->isMethod('HEAD') || $request->isMethod('OPTIONS')) {
            return true;
        }

        if ($request->is('up')) {
            return true;
        }

        return false;
    }
}
