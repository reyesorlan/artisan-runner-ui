<?php

namespace CodeSquirrel\ArtisanRunnerUI;

use CodeSquirrel\ArtisanRunnerUI\Http\Middleware\EnsureEnvironmentAllowed;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ArtisanRunnerUIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/artisan-runner-ui.php', 'artisan-runner-ui');
        $this->app->singleton(CommandCatalog::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'artisan-runner-ui');

        $this->publishes([
            __DIR__.'/../config/artisan-runner-ui.php' => config_path('artisan-runner-ui.php'),
        ], 'artisan-runner-ui-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/artisan-runner-ui'),
        ], 'artisan-runner-ui-views');

        if (config('artisan-runner-ui.enabled')) {
            Route::group([
                'prefix' => config('artisan-runner-ui.path'),
                'middleware' => array_merge(
                    [EnsureEnvironmentAllowed::class],
                    config('artisan-runner-ui.middleware', [])
                ),
            ], fn () => $this->loadRoutesFrom(__DIR__.'/../routes/web.php'));
        }
    }
}