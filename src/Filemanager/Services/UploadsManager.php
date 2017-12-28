<?php
namespace Iemand002\Filemanager\Services;

use Carbon\Carbon;
use Dflydev\ApacheMimeTypes\PhpRepository;
use Exception;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class UploadsManager
{
    protected $disk;
    protected $mimeDetect;

    public function __construct(PhpRepository $mimeDetect, ImageManager $intervention)
    {
        $this->disk = Storage::disk(config('filemanager.uploads.storage'));
        $this->mimeDetect = $mimeDetect;
        $this->library = config('imageupload.library', 'gd');
        $this->quality = config('imageupload.quality', 90);
        $this->intervention = $intervention;

        $this->intervention->configure(['driver' => $this->library]);
    }

    /**
     * Return files and directories within a folder
     *
     * @param string $folder
     * @return array of [
     *    'folder' => 'path to current folder',
     *    'folderName' => 'name of just current folder',
     *    'breadCrumbs' => breadcrumb array of [ $path => $foldername ]
     *    'folders' => array of [ $path => $foldername] of each subfolder
     *    'files' => array of file details on each file in folder
     * ]
     */
    public function folderInfo($folder)
    {
        $folder = $this->cleanFolder($folder);

        $breadcrumbs = $this->breadcrumbs($folder);
        $slice = array_slice($breadcrumbs, -1);
        $folderName = current($slice);
        $breadcrumbs = array_slice($breadcrumbs, 0, -1);

        $subfolders = [];
        foreach (array_unique($this->disk->directories($folder)) as $subfolder) {
            $subfolders["/$subfolder"] = basename($subfolder);
        }

        $files = [];
        foreach ($this->disk->files($folder) as $path) {
            $files[] = $this->fileDetails($path);
        }

        return compact(
            'folder',
            'folderName',
            'breadcrumbs',
            'subfolders',
            'files'
        );
    }

    /**
     * Sanitize the folder name
     */
    protected function cleanFolder($folder)
    {
        return '/' . trim(str_replace('..', '', $folder), '/');
    }

    /**
     * Return breadcrumbs to current folder
     */
    protected function breadcrumbs($folder)
    {
        $folder = trim($folder, '/');
        $crumbs = ['/' => 'root'];

        if (empty($folder)) {
            return $crumbs;
        }

        $folders = explode('/', $folder);
        $build = '';
        foreach ($folders as $folder) {
            $build .= '/'.$folder;
            $crumbs[$build] = $folder;
        }

        return $crumbs;
    }

    /**
     * Return an array of file details for a file
     */
    public function fileDetails($path)
    {
        $path = '/' . ltrim($path, '/');

        return [
            'name' => basename($path),
            'fullPath' => $path,
            'webPath' => $this->fileWebpath($path),
            'mimeType' => $this->fileMimeType($path),
            'size' => $this->fileSize($path),
            'modified' => $this->fileModified($path),
        ];
    }

    /**
     * Return the full web path to a file
     */
    public function fileWebpath($path)
    {
        $path = rtrim(config('filemanager.uploads.webpath'), '/') . '/' .
            ltrim($path, '/');
        return url($path);
    }

    /**
     * Return the mime type
     */
    public function fileMimeType($path)
    {
        return $this->mimeDetect->findType(
            pathinfo($path, PATHINFO_EXTENSION)
        );
    }

    /**
     * Return the file size
     */
    public function fileSize($path)
    {
        return $this->disk->size($path);
    }

    /**
     * Return the last modified time
     */
    public function fileModified($path)
    {
        return Carbon::createFromTimestamp(
            $this->disk->lastModified($path)
        );
    }

    /**
     * Create a new directory
     */
    public function createDirectory($folder)
    {
        $folder = $this->cleanFolder($folder);

        if ($this->disk->exists($folder)) {
            return trans('filemanager::filemanager.folder_exists',['folder'=>$folder]);
        }

        return $this->disk->makeDirectory($folder);
    }

    /**
     * Delete a directory
     */
    public function deleteDirectory($folder)
    {
        $folder = $this->cleanFolder($folder);

        $filesFolders = array_merge(
            $this->disk->directories($folder),
            $this->disk->files($folder)
        );
        if (! empty($filesFolders)) {
            return trans('filemanager::filemanager.folder_must_be_empty');
        }

        return $this->disk->deleteDirectory($folder);
    }

    /**
     * Delete a file
     */
    public function deleteFile($path)
    {
        $path = $this->cleanFolder($path);

        if (! $this->disk->exists($path)) {
            return trans('filemanager::filemanager.file_not_exist');
        }

        return $this->disk->delete($path);
    }

    /**
     * Save a file
     */
    public function saveFile($path, $content)
    {
        $path = $this->cleanFolder($path);

        if ($this->disk->exists($path)) {
            return trans('filemanager::filemanager.file_exits');
        }

        return $this->disk->put($path, $content);
    }

    /**
     * Resize file to create thumbnail.
     *
     * @access public
     * @param  UploadedFile $uploadedFile
     * @param  string       $targetFilepath
     * @param  int          $width
     * @param  int          $height         (default: null)
     * @param  bool         $squared        (default: false)
     * @return array
     */
    public function resizeCropImage($uploadedFile, $targetFilepath, $width, $height = null, $squared = false)
    {
//        dd($uploadedFile);
        try {
            $height = (! empty($height) ? $height : $width);
            $squared = (isset($squared) ? $squared : false);
dd(phpinfo());
            $image = $this->intervention->make($uploadedFile);

            if ($squared) {
                $width = ($height < $width ? $height : $width);
                $height = $width;

                $image->fit($width, $height, function ($image) {
                    $image->upsize();
                });
            } else {
                $image->resize($width, $height, function ($image) {
                    $image->aspectRatio();
                });
            }

            $image->save($targetFilepath, $this->quality);

            // Save to s3
//            $s3_url = $this->saveToS3($image, $targetFilepath);
return [];
//            return [
//                'path' => dirname($targetFilepath),
//                'dir' => $this->getRelativePath($targetFilepath),
//                'filename' => pathinfo($targetFilepath, PATHINFO_BASENAME),
//                'filepath' => $targetFilepath,
//                'filedir' => $this->getRelativePath($targetFilepath),
//                'width' => (int) $image->width(),
//                'height' => (int) $image->height(),
//                'filesize' => (int) $image->filesize(),
//                'is_squared' => (bool) $squared,
//            ];
        } catch (Exception $e) {
            throw new \Mockery\Exception($e->getMessage());
        }
    }
}