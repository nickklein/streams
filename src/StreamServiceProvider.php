<?php

namespace NickKlein\Streams;

use Illuminate\Support\ServiceProvider;
use NickKlein\Streams\Commands\RunSeederCommand;

class StreamServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        $this->loadRoutesFrom(__DIR__ . '/Routes/auth.php');

        // Register migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');

        // Publish 
        //
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/js/' => resource_path('js/Pages/Stream'),
            ], 'assets');
        }

        $this->commands([
            RunSeederCommand::class,
        ]);
    }
}
