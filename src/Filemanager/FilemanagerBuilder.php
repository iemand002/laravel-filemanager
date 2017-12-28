<?php

namespace Iemand002\Filemanager;

//use BadMethodCallException;
//use Dflydev\ApacheMimeTypes\PhpRepository;
//use Exception;
//use Iemand002\Filemanager\models\Uploads;
//use Illuminate\Support\Facades\Storage;
//use Illuminate\Support\Traits\Macroable;
//use Intervention\Image\ImageManager;

use BadMethodCallException;
use Dflydev\ApacheMimeTypes\PhpRepository;
use Exception;
use Iemand002\Filemanager\models\Uploads;
use Iemand002\Filemanager\Services\UploadsManager;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Traits\Macroable;
use Intervention\Image\ImageManager;

class FilemanagerBuilder
{

    use Macroable, Componentable {
        Macroable::__call as macroCall;
        Componentable::__call as componentCall;
    }

    protected $manager, $disk;

    public function __construct()
    {
        $this->manager = new Services\UploadsManager(new PhpRepository(),new ImageManager());
        $this->disk = Storage::disk(config('filemanager.uploads.storage'));
    }

    public function getUrl($id, $transformHandle = null){
        $transforms = config('filemanager.transforms');
        $transform = $transforms[$transformHandle];
        $upload = Uploads::find($id);
//        $uploadManager = new Services\UploadsManager(new PhpRepository(),new ImageManager());
        if (empty($transform) || ! is_array($transform) || $upload == null) {
            throw new Exception("file not found");
        }

        $folder = str_finish($upload->folder, '/');
        $transformFolder = str_finish($folder. '_'.$transformHandle , '/');
        $path = $transformFolder . $upload->filename;
        if (!$this->disk->exists($path)){
            $originalPath = $folder . $upload->filename;
            list($width, $height, $squared) = $transform;

            if (!$this->disk->exists($transformFolder)) {
                $this->disk->makeDirectory($transformFolder);
            }
//            dump($originalPath);
//            dd(Storage::disk(config('filemanager.uploads.storage'))->get($originalPath));
            $this->manager->resizeCropImage($this->disk->get($originalPath), $path, $width, $height, $squared);
        }

        return $this->manager->fileWebpath($path);
    }

    /**
     * Dynamically handle calls to the class.
     *
     * @param  string $method
     * @param  array  $parameters
     *
     * @return \Illuminate\Contracts\View\View|mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        try {
            return $this->componentCall($method, $parameters);
        } catch (BadMethodCallException $e) {
            //
        }

        try {
            return $this->macroCall($method, $parameters);
        } catch (BadMethodCallException $e) {
            //
        }

        throw new BadMethodCallException("Method {$method} does not exist.");
    }
}
