<?php

namespace Iemand002\Filemanager\Services;

use Carbon\Carbon;
use Dflydev\ApacheMimeTypes\PhpRepository;
use Exception;
use Iemand002\Filemanager\models\Uploads;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadsManager
{
    protected $disk;
    protected $mimeDetect;

    public function __construct(PhpRepository $mimeDetect, ImageManager $intervention)
    {
        $this->disk = Storage::disk(config('filemanager.uploads.storage'));
        $this->tempFolder = public_path(config('filemanager.uploads.temp', 'temp'));
        $this->mimeDetect = $mimeDetect;
        $this->library = config('filemanager.library', 'gd');
        $this->quality = config('filemanager.quality', 90);
        $this->intervention = $intervention;

        $this->intervention->configure(['driver' => $this->library]);
    }

    /**
     * Return files and directories within a folder found in the database
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

        $uploads = Uploads::where('folder', str_finish($folder, '/'))->get();

        $subfolders = [];
        foreach (array_unique($this->disk->directories($folder)) as $subfolder) {
            if (!starts_with(basename($subfolder), '_')) {
                $subfolders["/$subfolder"] = basename($subfolder);
            }
        }

        $files = [];
        foreach ($uploads as $upload) {
            $files[] = $this->fileDetails($upload);
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
     * Return files and directories within a folder found on the disk
     *
     * @param string $folder
     * @return array of [
     *    'folder' => 'path to current folder',
     *    'folders' => array of [ $path => $foldername] of each subfolder
     *    'files' => array of file details on each file in folder
     * ]
     */
    public function folderInfoDisk($folder)
    {
        $folder = $this->cleanFolder($folder);

        $subfolders = [];
        foreach (array_unique($this->disk->directories($folder)) as $subfolder) {
            if (!starts_with(basename($subfolder), '_')) {
                $subfolders["/$subfolder"] = basename($subfolder);
            }
        }

        $files = [];
        foreach ($this->disk->files($folder) as $path) {
            $files[] = $this->fileDetailsDisk($path);
        }

        return compact(
            'folder',
            'subfolders',
            'files'
        );
    }

    /**
     * Sanitize the folder name
     *
     * @param $folder
     * @return string
     */
    protected function cleanFolder($folder)
    {
        return '/' . trim(str_replace('..', '', $folder), '/');
    }

    /**
     * Return breadcrumbs to current folder
     *
     * @param $folder
     * @return array
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
            $build .= '/' . $folder;
            $crumbs[$build] = $folder;
        }

        return $crumbs;
    }

    /**
     * Return an array of file details for a file found in the database
     *
     * @param Uploads $upload
     * @return array
     */
    public function fileDetails(Uploads $upload)
    {
        $path = $upload->folder . $upload->filename;

        return [
            'id' => $upload->id,
            'name' => $upload->filename,
            'fullPath' => $path,
            'webPath' => $this->fileWebpath($path),
            'mimeType' => $upload->mimeType,
            'size' => $this->fileSize($path),
            'modified' => $this->fileModified($path),
        ];
    }

    /**
     * Return an array of file details for a file
     *
     * @param $path
     * @return array
     */
    public function fileDetailsDisk($path)
    {
        $path = '/' . ltrim($path, '/');

        return [
            'name' => basename($path),
            'mimeType' => $this->fileMimeType($path),
        ];
    }

    /**
     * Return the full web path to a file
     *
     * @param $path
     * @return \Illuminate\Contracts\Routing\UrlGenerator|string
     */
    public function fileWebpath($path)
    {
        $path = rtrim(config('filemanager.uploads.webpath'), '/') . '/' .
            ltrim($path, '/');
        return url($path);
    }

    /**
     * Return the mime type
     *
     * @param $path
     * @return mixed|null|string
     */
    public function fileMimeType($path)
    {
        return $this->mimeDetect->findType(
            pathinfo(strtolower($path), PATHINFO_EXTENSION)
        );
    }

    /**
     * Return the file size
     *
     * @param $path
     * @return
     */
    public function fileSize($path)
    {
        return $this->disk->size($path);
    }

    /**
     * Return the last modified time
     *
     * @param $path
     * @return Carbon
     */
    public function fileModified($path)
    {
        return Carbon::createFromTimestamp(
            $this->disk->lastModified($path)
        );
    }

    /**
     * Create a new directory
     *
     * @param $folder
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    public function createDirectory($folder)
    {
        $folder = $this->cleanFolder($folder);

        if ($this->disk->exists($folder)) {
            return trans('filemanager::filemanager.folder_exists', ['folder' => $folder]);
        }

        return $this->disk->makeDirectory($folder);
    }

    /**
     * Delete a directory
     *
     * @param $folder
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    public function deleteDirectory($folder)
    {
        $folder = $this->cleanFolder($folder);

        $filesFolders = array_merge(
            $this->disk->directories($folder),
            $this->disk->files($folder)
        );
        if (!empty($filesFolders)) {
            return trans('filemanager::filemanager.folder_must_be_empty');
        }

        return $this->disk->deleteDirectory($folder);
    }

    /**
     * Delete a file
     *
     * @param $path
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    public function deleteFile($path)
    {
        $path = $this->cleanFolder($path);

        if (!$this->disk->exists($path)) {
            return trans('filemanager::filemanager.file_not_exist');
        }

        return $this->disk->delete($path);
    }

    /**
     * Save a file
     *
     * @param $path
     * @param $content
     * @return string|\Symfony\Component\Translation\TranslatorInterface
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
     * @param  string $targetFilepath
     * @param  int $width
     * @param  int $height (default: null)
     * @param  int $quality (default: null)
     * @param  bool $squared (default: false)
     */
    public function resizeCropImage($uploadedFile, $targetFilepath, $filename, $width, $height = null, $quality = null, $squared = false)
    {
        try {
            $height = (!empty($height) ? $height : $width);
            $quality = (!empty($quality) ? $quality : $this->quality);
            $squared = (isset($squared) ? $squared : false);

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

            $tempFile = str_finish($this->tempFolder, '/') . $filename;

            mkdir($this->tempFolder);
            $image->save($tempFile, $quality);
            $this->saveFile($targetFilepath, $image);
            $this->removeTemp();

        } catch (Exception $e) {
            throw new \Mockery\Exception($e->getMessage());
        }
    }

    /**
     * Remove the temporary created folder and files
     */
    private function removeTemp()
    {
        $it = new RecursiveDirectoryIterator($this->tempFolder, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it,
            RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($this->tempFolder);
    }
}