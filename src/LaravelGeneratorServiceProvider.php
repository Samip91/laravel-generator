<?php

namespace Brikshya\LaravelGenerator;

use Illuminate\Support\ServiceProvider;
use Brikshya\LaravelGenerator\Commands\MakeModuleCommand;
use Brikshya\LaravelGenerator\Commands\MakeModelPlusCommand;
use Brikshya\LaravelGenerator\Commands\MakeControllerPlusCommand;
use Brikshya\LaravelGenerator\Commands\MakeViewPlusCommand;
use Brikshya\LaravelGenerator\Commands\MakeRequestPlusCommand;
use Brikshya\LaravelGenerator\Commands\MakeResourcePlusCommand;

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
                MakeModelPlusCommand::class,
                MakeControllerPlusCommand::class,
                MakeViewPlusCommand::class,
                MakeRequestPlusCommand::class,
                MakeResourcePlusCommand::class,
            ]);

            // Publish configuration file
            $this->publishes([
                __DIR__.'/../config/laravel-generator.php' => config_path('laravel-generator.php'),
            ], 'laravel-generator-config');

            // Publish stub files for customization
            $this->publishes([
                __DIR__.'/Stubs' => resource_path('stubs/laravel-generator'),
            ], 'laravel-generator-stubs');

            // Publish assets
            $this->publishes([
                __DIR__.'/../resources/js' => public_path('vendor/laravel-generator/js'),
                __DIR__.'/../resources/css' => public_path('vendor/laravel-generator/css'),
            ], 'laravel-generator-assets');

            // Publish dev assets (for Vite bundling)
            $this->publishes([
                __DIR__.'/../resources/js' => resource_path('js/vendor/laravel-generator'),
                __DIR__.'/../resources/css' => resource_path('css/vendor/laravel-generator'),
            ], 'laravel-generator-dev-assets');
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            MakeModuleCommand::class,
            MakeModelPlusCommand::class,
            MakeControllerPlusCommand::class,
            MakeViewPlusCommand::class,
            MakeRequestPlusCommand::class,
            MakeResourcePlusCommand::class,
        ];
    }
}