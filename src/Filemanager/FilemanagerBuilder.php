<?php

namespace Iemand002\Filemanager;

use Dflydev\ApacheMimeTypes\PhpRepository;
use Iemand002\Filemanager\models\Uploads;
use Iemand002\Filemanager\Traits\DropboxHelperTrait;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Image;
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
     * @throws FileNotFoundException
     */
    public function getFile($id, $transformHandle = null)
    {
        $upload = Uploads::with('transforms')->find($id);

        if ($upload == null) {
            return null;
        }

        $folder = Str::finish($upload->folder, '/');
        $path = $folder . $upload->filename;

        if ($upload->provider) {
            if ($transformHandle) {
                return getenv('APP_URL') . $upload->provider . '/_' . $transformHandle . '/' . $path;
            } else {
                return getenv('APP_URL') . $upload->provider . '/' . $path;
            }
        }

        if (is_image($upload->mimeType)) {
            if ($transformHandle != null) {
                return json_decode($this->getImage($upload, $transformHandle));
            }

            $upload->width = $upload->dimension->width;
            $upload->height = $upload->dimension->height;
        }

        $upload->url = $this->disk->url($path);
        return $upload;
    }

    /**
     * Get the url of an uploaded file
     *
     * @param $id
     * @param null $transformHandle
     * @return string|null
     * @throws FileNotFoundException
     */
    public function getUrl($id, $transformHandle = null): ?string
    {
        return $this->getFile($id, $transformHandle)->url ?? null;
    }

    /**
     * @throws FileNotFoundException
     */
    public function getWidth($id, $transformHandle = null): int
    {
        return $this->getFile($id, $transformHandle)->width ?? 0;
    }

    /**
     * @throws FileNotFoundException
     */
    public function getHeight($id, $transformHandle = null): int
    {
        return $this->getFile($id, $transformHandle)->height ?? 0;
    }

    /**
     * @param $transformHandle
     * @param $folder
     * @param $upload
     * @param $transform
     * @return string
     * @throws FileNotFoundException
     */
    private function makeTransform($transformHandle, $folder, $upload, $transform): string
    {
        $transformFolder = Str::finish($folder . '_' . $transformHandle, '/');
        $path = $transformFolder . $upload->filename;
        $transformImage = $upload->transforms->keyBy('transform')[$transformHandle] ?? null;

        if (!$transformImage) {
            $originalPath = $folder . $upload->filename;

            if (!$this->disk->exists($transformFolder)) {
                $this->disk->makeDirectory($transformFolder);
            }

            $this->manager->resizeCropImage($this->disk->get($originalPath), $path, $upload, $transform, $transformHandle);
            $transformImage = $upload->transforms->keyBy('transform')[$transformHandle] ?? null;

        }

        $transformImage->filename = $upload->filename;
        $transformImage->url = $this->disk->url($path);
        $transformImage->width = $transformImage->dimension->width;
        $transformImage->height = $transformImage->dimension->height;
        return $transformImage;
    }

    /**
     * @param $upload
     * @param null $transformHandle
     * @return string|null
     * @throws FileNotFoundException
     */
    private function getImage($upload, $transformHandle = null): ?string
    {
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

        $folder = Str::finish($upload->folder, '/');

        if ($transformHandle != null) {
            return $this->makeTransform($transformHandle, $folder, $upload, $transform);
        }

        return $upload;
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
    private function resize_dimensions($goal_width, $goal_height, $width, $height): array
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