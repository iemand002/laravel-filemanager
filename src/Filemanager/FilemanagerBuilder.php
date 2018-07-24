<?php

namespace Iemand002\Filemanager;

use Dflydev\ApacheMimeTypes\PhpRepository;
use Iemand002\Filemanager\models\Uploads;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class FilemanagerBuilder
{
    protected $manager, $disk, $intervention;

    /**
     * FilemanagerBuilder constructor.
     */
    public function __construct()
    {
        $this->intervention = new ImageManager();
        $this->manager = new Services\UploadsManager(new PhpRepository(), $this->intervention);
        $this->disk = Storage::disk(config('filesystems.' . config('filemanager.uploads.storage')));
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

        if (is_image($upload->mimeType) && $transformHandle != null) {
            $transforms = config('filemanager.transforms');
            $transform = $transforms[$transformHandle];

            if (empty($transform) || !is_array($transform)) {
                return null;
            }

            $path = $this->makeTransform($transformHandle, $folder, $upload, $transform);
        }

        return $this->disk->url($path);
    }

    public function getWidth($id, $transformHandle = null)
    {
        return $this->getImage($id,$transformHandle)->width();
    }

    public function getHeight($id, $transformHandle = null)
    {
        return $this->getImage($id,$transformHandle)->height();
    }

    /**
     * @param $transformHandle
     * @param $folder
     * @param $upload
     * @param $transform
     * @return string
     */
    private function makeTransform($transformHandle, $folder, $upload, $transform): string
    {
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
        return $path;
    }

    private function getImage($id, $transformHandle = null){
        $upload = Uploads::find($id);

        if ($upload == null || !is_image($upload->mimeType)) {
            return null;
        }

        $folder = str_finish($upload->folder, '/');
        $path = $folder . $upload->filename;

        if ($transformHandle != null){
            $transforms = config('filemanager.transforms');
            $transform = $transforms[$transformHandle];

            if (empty($transform) || !is_array($transform)) {
                return null;
            }

            $path = $this->makeTransform($transformHandle, $folder, $upload, $transform);
        }

        return $this->intervention->make($this->disk->get($path));
    }
}
