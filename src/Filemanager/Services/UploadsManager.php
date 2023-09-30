<?php

namespace Iemand002\Filemanager\Services;

use Carbon\Carbon;
use Dflydev\ApacheMimeTypes\PhpRepository;
use Exception;
use Iemand002\Filemanager\models\Transforms;
use Iemand002\Filemanager\models\Uploads;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadsManager
{
    protected $disk;
    protected $mimeDetect;
    /**
     * @var string
     */
    private $tempFolder;
    /**
     * @var Repository|Application|mixed
     */
    private $library;
    /**
     * @var Repository|Application|mixed
     */
    private $quality;
    /**
     * @var ImageManager
     */
    private $intervention;

    /**
     * UploadsManager constructor.
     * @param PhpRepository $mimeDetect
     * @param ImageManager $intervention
     */
    public function __construct(PhpRepository $mimeDetect, ImageManager $intervention)
    {
        $this->disk = Storage::disk(config('filesystems.' . config('filemanager.uploads.storage')));
        $this->tempFolder = public_path(config('filemanager.uploads.temp', 'temp'));
        $this->mimeDetect = $mimeDetect;
        $this->library = config('filemanager.library', 'gd');
        $this->quality = config('filemanager.quality', 90);
        $this->intervention = $intervention;

        $this->intervention->configure(['driver' => $this->library]);
    }

//    /**
//     * Return files and directories within a folder found in the database
//     *
//     * @param string $folder
//     * @return array of [
//     *    'folder' => 'path to current folder',
//     *    'folderName' => 'name of just current folder',
//     *    'breadCrumbs' => breadcrumb array of [ $path => $foldername ]
//     *    'folders' => array of [ $path => $foldername] of each subfolder
//     *    'files' => array of file details on each file in folder
//     * ]
//     */
//    public function folderInfo($folder)
//    {
//        $folder = $this->cleanFolder($folder);
//
//        $breadcrumbs = $this->breadcrumbs($folder);
//        $slice = array_slice($breadcrumbs, -1);
//        $folderName = current($slice);
//        $breadcrumbs = array_slice($breadcrumbs, 0, -1);
//
//        $uploads = Uploads::where('folder', Str::finish($folder, '/'))->where('provider', null)->get();
//
//        $subfolders = [];
//        foreach (array_unique($this->disk->directories($folder)) as $subfolder) {
//            if (!Str::startsWith(basename($subfolder), '_')) {
//                $subfolders["/$subfolder"] = basename($subfolder);
//            }
//        }
//
//        $files = [];
//        foreach ($uploads as $upload) {
//            $files[] = $this->fileDetails($upload);
//        }
//
//        return compact(
//            'folder',
//            'folderName',
//            'breadcrumbs',
//            'subfolders',
//            'files'
//        );
//    }
//
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
    public function folderInfoDisk(string $folder): array
    {
        $folder = $this->cleanFolder($folder);

        $subFolders = [];
        foreach (array_unique($this->disk->directories($folder)) as $subFolder) {
            if (!Str::startsWith(basename($subFolder), '_')) {
                $subFolders["/$subFolder"] = basename($subFolder);
            }
        }

        $files = [];
        foreach ($this->disk->files($folder) as $path) {
            $files[] = $this->fileDetailsDisk($path, $folder);
        }

        return compact(
            'folder',
            'subFolders',
            'files'
        );
    }

    public function folderInfoSubfolders($folder): array
    {
        $folder = $this->cleanFolder($folder);

        $breadcrumbs = $this->breadcrumbs($folder);
        $slice = array_slice($breadcrumbs, -1);
        $folderName = current($slice);
        $breadcrumbs = array_slice($breadcrumbs, 0, -1);

        $subFolders = [];
        foreach (array_unique($this->disk->directories($folder)) as $subFolder) {
            if (!Str::startsWith(basename($subFolder), '_')) {
                $subFolders["/$subFolder"] = basename($subFolder);
            }
        }

        return compact(
            'folder',
            'folderName',
            'breadcrumbs',
            'subFolders',
        );
    }

    public function folderInfoUploads($folder): array
    {
        $folder = $this->cleanFolder($folder);
        $uploads = Uploads::where('folder', Str::finish($folder, '/'))->where('provider', null)->get();
        $files = [];
        foreach ($uploads as $upload) {
            $files[] = $this->fileDetails($upload);
        }
        return $files;
    }

    /**
     * Sanitize the folder name
     *
     * @param $folder
     * @return string
     */
    protected function cleanFolder($folder): string
    {
        return '/' . trim(str_replace('..', '', $folder), '/');
    }

    /**
     * Return breadcrumbs to current folder
     *
     * @param $folder
     * @return array
     */
    protected function breadcrumbs($folder): array
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
    public function fileDetails(Uploads $upload): array
    {
        $path = $upload->folder . $upload->filename;

        return [
            'id' => $upload->id,
            'name' => $upload->filename,
            'fullPath' => $path,
            'webPath' => $this->disk->url($path),
            'mimeType' => $upload->mimeType,
            'size' => $upload->size,
            'dimension' => $upload->dimension,
            'time_taken' => $upload->time_taken,
        ];
    }

    public function diskFilePath($path): string
    {
        return $this->disk->path($path);
    }

    /**
     * Return an array of file details for a file
     *
     * @param $path
     * @param $folder
     * @return array
     */
    public function fileDetailsDisk($path, $folder): array
    {
        $path = '/' . ltrim($path, '/');

        $mimeType = $this->disk->mimeType($path);

        return [
            'name' => basename($path),
            'folder' => Str::finish($folder, '/'),
            'path' => $path,
            'type' => $mimeType,
            'size' => $this->disk->size($path),
        ];
    }

    /**
     * Return the last modified time
     *
     * @param $path
     * @return Carbon
     */
    public function fileModified($path): Carbon
    {
        return Carbon::createFromTimestamp(
            $this->disk->lastModified($path)
        );
    }

    /**
     * Create a new directory
     *
     * @param $folder
     * @param $path
     * @return Application|array|bool|string|Translator|null
     */
    public function createDirectory($folder, $path)
    {
        $folder = Str::slug($this->cleanFolder($folder));
        $path = $path . '/' . $folder;

        if ($this->disk->exists($path)) {
            return trans('filemanager::filemanager.folder_exists', ['folder' => $path]);
        }

        return $this->disk->makeDirectory($path);
    }

    /**
     * Delete a directory
     *
     * @param $folder
     * @return array|bool|Application|Translator|string|null
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
     * @return Application|array|bool|string|Translator|null
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
     * @return Application|array|bool|string|Translator|null
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
     * @param string $originalFilepath
     * @param string $targetFilepath
     * @param Uploads $upload
     * @param array $transform
     * @param string $transformHandle
     */
    public function resizeCropImage(
        string       $originalFile,
        string       $targetFilepath,
        Uploads      $upload,
        array        $transform,
        string       $transformHandle
    )
    {
        list($width, $height, $squared, $quality) = $transform;

        try {
            $quality = (!empty($quality) ? $quality : $this->quality);
            $squared = $squared ?? false;

            $image = $this->intervention->make($originalFile);

            if ($squared) {
                $width = min($height, $width);
                $height = $width;

                $image->fit($width, $height, function ($image) {
                    $image->upsize();
                });
            } else {
                $image->resize($width, $height, function ($image) {
                    $image->aspectRatio();
                });
            }

            $tempFile = Str::finish($this->tempFolder, '/') . $upload->filename;

            mkdir($this->tempFolder);
            $image->save($tempFile, $quality);
            $this->saveTransformToDb($image, $transformHandle, $upload);
            $this->saveFile($targetFilepath, $image);
            $this->removeTemp();

        } catch (Exception $e) {
            throw new \Mockery\Exception($e->getMessage());
        }
    }

    private function saveTransformToDb($image, $transformHandle, $upload)
    {
        $transform = new Transforms();
        $transform->transform = $transformHandle;
        $transform->dimension = ['width' => $image->width(), 'height' => $image->height()];
        $upload->transforms()->save($transform);
        $upload->refresh();
    }

    /**
     * Remove the temporary created folder and files
     */
    private function removeTemp()
    {
//        $it = new RecursiveDirectoryIterator($this->tempFolder, RecursiveDirectoryIterator::SKIP_DOTS);
//        $files = new RecursiveIteratorIterator($it,
//            RecursiveIteratorIterator::CHILD_FIRST);
//        foreach ($files as $file) {
//            if ($file->isDir()) {
//                rmdir($file->getRealPath());
//            } else {
//                unlink($file->getRealPath());
//            }
//        }
//        rmdir($this->tempFolder);

        $path = $this->tempFolder;
        if (file_exists($path) === false) {
            return;
        }
        if (is_file($path)) {
            unlink($path);
            return;
        }

        $files = scandir($path);

        foreach ($files as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $path . '/' . $item;

            if (is_file($fullPath)) {
                unlink($fullPath);
            }
        }

        rmdir($path);
    }
}