# laravel-filemanager
Multilanguage filemanager package based on http://laravelcoding.com/blog/laravel-5-beauty-upload-manager?tag=L5+Beauty

## Installation
### Requirements
* Laravel 5.1+
* Twitter Bootstrap
* Jquery

### Composer
```bash
composer require iemand002/filemanager dev-master
```

### Laravel
After that register the provider in your app.php config
```php
Iemand002\Filemanager\FilemanagerServiceProvider::class,
```

Add or edit in filesystems.php config the following disk
```json
        'public' => [
            'driver' => 'local',
            'root' => public_path(config('filemanager.uploads.webpath')),
            'visibility' => 'public',
        ],
```

## Publish configuration
The configuration file (filemanager.php)
```bash
php artisan vendor:publish --provider="Iemand002\Filemanager\FilemanagerServiceProvider" --tag="config"
```

The views (vendor/iemand002/filemanager)
```bash
php artisan vendor:publish --provider="Iemand002\Filemanager\FilemanagerServiceProvider" --tag="views"
```

## How to use
Just surf to ```yourwebsite.io/admin/upload```<br/>
By default it has the ```web``` middleware for Laravel 5.2<br/>
To change the required middlewares and the prefix change the config file

## Build-in languages
* Dutch (nl)
* English (en)