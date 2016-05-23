<?php

namespace Iemand002\Filemanager;

use Illuminate\Support\ServiceProvider;

class FilemanagerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__ . '/lang', 'filemanager');
        $this->loadViewsFrom(__DIR__ . '/views', 'iemand002/filemanager');
        $this->publishes([
            __DIR__ . '/views' => base_path('resources/views/vendor/iemand002/filemanager')
        ], 'views');
        $this->publishes([
            __DIR__ . '/config/filemanager.php' => config_path('filemanager.php')
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/filemanager.php', 'filemanager'
        );
        include __DIR__ . '/routes.php';
        include __DIR__ . '/filemanager-helpers.php';
        $this->app->make('Iemand002\Filemanager\Controllers\UploadController');
    }
}
