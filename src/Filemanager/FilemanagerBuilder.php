<?php

namespace Iemand002\Filemanager;

use Dflydev\ApacheMimeTypes\PhpRepository;
use Iemand002\Filemanager\models\Uploads;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class FilemanagerBuilder
{
    protected $manager, $disk;

    public function __construct()
    {
        $this->manager = new Services\UploadsManager(new PhpRepository(), new ImageManager());
        $this->disk = Storage::disk(config('filemanager.uploads.storage'));
    }

    public function getUrl($id, $transformHandle = null)
    {
        $transforms = config('filemanager.transforms');
        $transform = $transforms[$transformHandle];
        $upload = Uploads::find($id);

        if (empty($transform) || !is_array($transform) || $upload == null) {
//            throw new Exception("file not found");
            return null;
        }

        $folder = str_finish($upload->folder, '/');
        $transformFolder = str_finish($folder . '_' . $transformHandle, '/');
        $path = $transformFolder . $upload->filename;
        if (!$this->disk->exists($path)) {
            $originalPath = $folder . $upload->filename;
            list($width, $height, $squared, $quality) = $transform;

            if (!$this->disk->exists($transformFolder)) {
                $this->disk->makeDirectory($transformFolder);
            }

            $this->manager->resizeCropImage($this->disk->get($originalPath), $path, $upload->filename, $width, $height, $quality, $squared);
        }

        return $this->manager->fileWebpath($path);
    }
}
