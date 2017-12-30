<?php

namespace Iemand002\Filemanager;

use Dflydev\ApacheMimeTypes\PhpRepository;
use Iemand002\Filemanager\Services\UploadsManager;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\ImageManager;

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
        $this->publishes([
            __DIR__ . '/database/migrations' => base_path('database/migrations')
        ], 'migration');
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

        $this->registerHtmlBuilder();

        $this->app->alias('filemanager', 'Iemand002\Filemanager\FilemangerBuilder');
    }

    /**
     * Register the HTML builder instance.
     *
     * @return void
     */
    protected function registerHtmlBuilder()
    {
        $this->app->singleton('filemanager', function ($app) {
            return new FilemanagerBuilder();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['filemanager', 'Iemand002\Filemanager\FilemangerBuilder'];
    }
}
