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
        if(config('filemanager.uploads.storage') == 'cloud') {
            $this->disk = Storage::disk(config('filesystems.cloud'));
        } else {
            $this->disk = Storage::disk(config('filesystems.default'));
        }
    }

    /**
     * Get the url of a uploaded file
     *
     * @param $id
     * @param null $transformHandle
     * @return \Illuminate\Contracts\Routing\UrlGenerator|null|string
     */
    public function getUrl($id, $transformHandle = null)
    {
        $upload = Uploads::find($id);

        if ($upload == null) {
            return null;
        }

        $folder = str_finish($upload->folder, '/');
        $path = $folder . $upload->filename;

        if(starts_with($upload->mimeType,'image')&&$transformHandle!=null){
            $transforms = config('filemanager.transforms');
            $transform = $transforms[$transformHandle];

            if (empty($transform) || !is_array($transform) || $upload == null) {
                return null;
            }

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
        }

        return $this->disk->url($path);
    }
}
