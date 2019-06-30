<?php

namespace Iemand002\Filemanager;

use Dflydev\ApacheMimeTypes\PhpRepository;
use Iemand002\Filemanager\models\Uploads;
use Iemand002\Filemanager\Traits\DropboxHelperTrait;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class FilemanagerBuilder
{
    use DropboxHelperTrait;

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

        if ($upload->provider) {
            if ($transformHandle) {
                return getenv('APP_URL') . $upload->provider . '/_' . $transformHandle . '/' . $path;
            } else {
                return getenv('APP_URL') . $upload->provider . '/' . $path;
            }
        }

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
        return $this->getImage($id, $transformHandle) ? $this->getImage($id, $transformHandle)->width() : 0;
    }

    public function getHeight($id, $transformHandle = null)
    {
        return $this->getImage($id, $transformHandle) ? $this->getImage($id, $transformHandle)->height() : 0;
    }

    /**
     * @param $transformHandle
     * @param $folder
     * @param $upload
     * @param $transform
     */
    private function makeTransform($transformHandle, $folder, $upload, $transform)
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

    /**
     * @param $id
     * @param null $transformHandle
     * @return \Intervention\Image\Image|null
     */
    private function getImage($id, $transformHandle = null)
    {
        $upload = Uploads::find($id);

        if ($upload == null || !is_image($upload->mimeType)) {
            return null;
        }

        if ($transformHandle != null) {
            $transforms = config('filemanager.transforms');
            $transform = $transforms[$transformHandle];

            if (empty($transform) || !is_array($transform)) {
                return null;
            }
        }

        if ($upload->provider) {
            if ($upload->provider == 'dropbox') {
                $transform = $this->calculateDropboxTransform($upload, $transformHandle ?? null);
                $dimension = $this->resize_dimensions($transform['width'], $transform['height'], $upload->dimension->width, $upload->dimension->height);
            } else {
                list($width, $height) = config('filemanager.cloud_default_transform');
                $dimension = $this->resize_dimensions($transform[0] ?? $width, $transform[1] ?? $height, $upload->dimension->width, $upload->dimension->height);
            }
            $upload->dimension = $dimension;
            return $upload;
        }

        $folder = str_finish($upload->folder, '/');
        $path = $folder . $upload->filename;

        if ($transformHandle != null) {
            $path = $this->makeTransform($transformHandle, $folder, $upload, $transform);
        }

        return $this->intervention->make($this->disk->get($path));
    }

    /**
     * Calculates restricted dimensions with a maximum of $goal_width by $goal_height
     *
     * @param $goal_width
     * @param $goal_height
     * @param $width
     * @param $height
     * @return array
     */
    private function resize_dimensions($goal_width, $goal_height, $width, $height)
    {
        $return = array('width' => $width, 'height' => $height);

        // If the ratio > goal ratio and the width > goal width resize down to goal width
        if ($width / $height > $goal_width / $goal_height && $width > $goal_width) {
            $return['width'] = $goal_width;
            $return['height'] = (int)($goal_width / $width * $height);
        } // Otherwise, if the height > goal, resize down to goal height
        else if ($height > $goal_height) {
            $return['width'] = (int)($goal_height / $height * $width);
            $return['height'] = $goal_height;
        }

        return $return;
    }
}
