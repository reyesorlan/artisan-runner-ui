<?php

namespace CodeSquirrel\ArtisanRunnerUI\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEnvironmentAllowed
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(app()->environment(config('artisan-runner-ui.environments', [])), 404);

        return $next($request);
    }
}