# laravel-filemanager
* Multi language file manager package based on https://web.archive.org/web/20160425103612/http://laravelcoding.com:80/blog/laravel-5-beauty-upload-manager?tag=L5+Beauty
* Using ```intervention/image``` to create image transforms (inspired by ```matriphe/imageupload```).
* Now able to select multiple files at once using localstorage
* Integration with Dropbox for loading files directly from Dropbox

## Installation
### Requirements
* Laravel  
    * 5.2 (version 0.x), 
    * 5.5 (version 1.x/2.0), 
    * 5.6/5.7/5.8 (version 2.1)
    * 6.x/7.x (version 2.2)
* Twitter Bootstrap 4
* Jquery 3.5

### Composer
```bash
composer require iemand002/filemanager
```

### Register (when not using autodiscover)
Register the provider in your app.php config
```php
'providers' => [
    Iemand002\Filemanager\FilemanagerServiceProvider::class,
],

'aliases' => [
    'Filemanager' => Iemand002\Filemanager\FilemanagerFacade::class,
],
```

### Publish configurations
Configuration file  
<small>_config/filemanager.php_</small>
```bash
php artisan vendor:publish --provider="Iemand002\Filemanager\FilemanagerServiceProvider" --tag="config"
```

Migration file (optional)  
<small>_2017_12_29_004110_create_image_upload_table.php and 2018_05_12_161010_create_social_logins_table and 2019_02_17_141025_alter_image_upload_table.php_</small>
```bash
php artisan vendor:publish --provider="Iemand002\Filemanager\FilemanagerServiceProvider" --tag="migration"
```

Views  
<small>_vendor/iemand002/filemanager_</small>
```bash
php artisan vendor:publish --provider="Iemand002\Filemanager\FilemanagerServiceProvider" --tag="views"
```

Translations  
<small>_lang/vendor/filemanager_</small>
```bash
php artisan vendor:publish --provider="Iemand002\Filemanager\FilemanagerServiceProvider" --tag="translations"
```

### Configure Clouds: Dropbox/OneDrive (optional)
This package uses [`socialiteproviders/dropbox`](https://socialiteproviders.netlify.com/providers/dropbox.html) for the Dropbox login and/or [`socialiteproviders/graph`](https://socialiteproviders.netlify.com/providers/microsoft-graph.html). To get it working follow step 2 - 4 from there documentation.
<br><br>
After that add the relation to the `Social` model in your `User` model:
```php
    public function socials()
    {
        return $this->hasMany('Iemand002\Filemanager\Models\Social');
    }
```

## How to use
Run the migration using ```php artsan migrate```<br/>
Just surf to ```yourwebsite.io/admin/upload```<br/>
By default it has the ```web``` middleware for Laravel 5.2+.<br/>
To change the required middlewares and the prefix change the config file.
<br><br>
Get the url of the uploaded file in your blade template via the uploadId: ```{{ Filemanager::getUrl(123) }}```<br>
If you wish to show a transformed version of an image add the optional ```$transfromHandle```: ```{{ Filemanager::getUrl(123, "transformHandle") }}```<br>
<br>
Get the image width and height via the uploadId: ```{{ Filemanager::getWidth(123) }}``` and ```{{ Filemanager::getHeight(123) }}```<br>
Also compatable with the transform: ```{{ Filemanager::getWidth(123, "transformHandle") }}``` and ```{{ Filemanager::getHeight(123, "transformHandle") }}```<br>
<br>
Want to sync earlier uploaded files in the folder with the database? Surf to ```yourwebsite.io/admin/sync``` to add missing files in the database.

### Demo
Create a route like the following to see an example usage.
````php
Route::get('demo',function () {
    return view('iemand002/filemanager::demo.demo');
});
````

## Build-in languages
* Dutch (nl)
* English (en)

## TODO
* Find out what's wrong with the graph login, it works, but giving errors sometimes
* Cloud remove reference in database if file is removed from the cloud
* Add cloud views to index

## Changelog
2.2.1  
Fixed:  
* Filemanager `index.blade.php` styling fixes

Check previous changes in CHANGELOG.md