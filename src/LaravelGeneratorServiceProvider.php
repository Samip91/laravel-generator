<?php

namespace Brikshya\LaravelGenerator;

use Illuminate\Support\ServiceProvider;
use Brikshya\LaravelGenerator\Commands\MakeModuleCommand;

class LaravelGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/laravel-generator.php', 'laravel-generator'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModuleCommand::class,
            ]);

            // Publish configuration file
            $this->publishes([
                __DIR__.'/../config/laravel-generator.php' => config_path('laravel-generator.php'),
            ], 'laravel-generator-config');

            // Publish stub files for customization
            $this->publishes([
                __DIR__.'/Stubs' => resource_path('stubs/laravel-generator'),
            ], 'laravel-generator-stubs');
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            MakeModuleCommand::class,
        ];
    }
}