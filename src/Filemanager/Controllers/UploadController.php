<?php

namespace Iemand002\Filemanager\Controllers;

use Carbon\Carbon;
use Iemand002\Filemanager\Requests\UploadFileRequest;
use Iemand002\Filemanager\Requests\UploadNewFolderRequest;
use Iemand002\Filemanager\Services\UploadsManager;

use Iemand002\Filemanager\models\Uploads;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;


class UploadController extends Controller
{
    protected $manager;

    /**
     * UploadController constructor.
     * @param UploadsManager $manager
     */
    public function __construct(UploadsManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Show page of files / subfolders
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $folder = $request->get('folder');
        $data = $this->manager->folderInfoSubfolders($folder);
        $data['active'] = 'filemanager';

        return view('iemand002/filemanager::index', $data);
    }

    /**
     * Show page of files / subfolders
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function picker(Request $request)
    {
        $folder = $request->get('folder');
//        if (config('filemanager.jquery_datatables.use')){
            $data = $this->manager->folderInfoSubfolders($folder);
//        } else {
//            $data = $this->manager->folderInfo($folder);
//        }

        return view('iemand002/filemanager::picker', $data);
    }

    public function ajax(Request $request)
    {
        $folder = $request->get('folder');
        $rawData = $this->manager->folderInfoUploads($folder);
        $data = [];
        foreach ($rawData as $row) {
            $data[] = [
                'multi' => [
                  'id' => $row['id'],
                  'name' => $row['name'],
                ],
                'link' => [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'isImage' => is_image($row['mimeType']),
                ],
                'type' => $row['mimeType'] ?? 'Unknown',
                'modified' => [
                    'display' => $row['time_taken'] ? $row['time_taken']->format('j-M-y g:ia') : '',
                    'sort' => $row['time_taken'] ? $row['time_taken']->format('U') : '',
                ],
                'size' => human_filesize($row['size']),
                'buttons' => [
                    'name' => $row['name'],
                    'isImage' => is_image($row['mimeType']),
                    'webPath' => $row['webPath'],
                ],
            ];
        }

        return json_encode(['data'=>$data]);
    }

    /**
     * Sync files uploaded in the folder that are not in the database with the database
     *
     * @param string $folder
     * @return string
     */
    public function sync($folder = '/')
    {
        $data = $this->manager->folderInfoDisk($folder);

        foreach ($data['files'] as $file) {
            if (Uploads::where('filename', $file['name'])->where('folder', $data['folder'])->count() == 0) {
                $this->saveToDb($file, true);
            }
        }

        foreach ($data['subfolders'] as $subfolder => $key) {
            $this->sync($subfolder);
        }

        return 'Files synced: ' . date('Y-m-d H:i:s');
    }

    /**
     * Create a new folder
     *
     * @param UploadNewFolderRequest $request
     * @return RedirectResponse
     */
    public function createFolder(UploadNewFolderRequest $request)
    {
        $folder = $request->get('new_folder');
        $path = $request->get('path');

        $result = $this->manager->createDirectory($folder, $path);

        if ($result === true) {
            return redirect()
                ->back()
                ->withSuccess(trans('filemanager::filemanager.folder_created', ['folder' => $folder]));
        }

        $error = $result ?: trans('filemanager::filemanager.error_creating_folder');
        return redirect()
            ->back()
            ->withErrors([$error]);
    }

    /**
     * Delete a file
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function deleteFile(Request $request)
    {
        $del_file = $request->get('del_file');
        $result = $this->delFromDb($del_file, Str::finish($request->get('folder'), '/'));

        if ($result === true){
            $path = $request->get('folder') . '/' . $del_file;
            $result = $this->manager->deleteFile($path);

            if ($result === true){
                return redirect()
                    ->back()
                    ->withSuccess(trans('filemanager::filemanager.file_deleted', ['file' => $del_file]));
            }
        }

        $error = $result ?: trans('filemanager::filemanager.error_deleting_file');
        return redirect()
            ->back()
            ->withErrors([$error]);
    }

    /**
     * Delete a folder
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function deleteFolder(Request $request)
    {
        $path = $request->get('path');
        $del_folder = substr($path, strrpos($path, '/') + 1);
        $parent = $this->right_trim($path, $del_folder);

        $result = $this->manager->deleteDirectory($path);

        if ($result === true) {
            return redirect(str_replace($path, $parent, redirect()->back()->getTargetUrl()))
                ->withSuccess(trans('filemanager::filemanager.folder_deleted', ['folder' => Str::headline($del_folder)]));
        }

        $error = $result ?: trans('filemanager::filemanager.error_deleting_folder');
        return redirect()
            ->back()
            ->withErrors([$error]);
    }

    private function right_trim(string $haystack, string $needle): string {
        $needle_length = strlen($needle);
        if (substr($haystack, -$needle_length) === $needle) {
            return substr($haystack, 0, -$needle_length);
        }
        return $haystack;
    }

    /**
     * Upload new file
     *
     * @param UploadFileRequest $request
     * @return JsonResponse|RedirectResponse
     * @throws FileNotFoundException
     */
    public function uploadFile(UploadFileRequest $request)
    {
        $file = $_FILES['file'];
        $file_parts = pathinfo($file['name']);
        $fileName = $request->get('file_name');
        $fileName_parts = pathinfo($fileName);
        if ($fileName) {
            $fileName = $fileName_parts['filename'];
        } else {
            $fileName = $file_parts['filename'];
        }
        $fileName = Str::slug($fileName);
        $fileName .= '.' . $file_parts['extension'];
        $folder = Str::finish($request->get('folder'), '/');
        $path = $folder . $fileName;
        $content = File::get($file['tmp_name']);
        $file['name'] = $fileName;
        $file['path'] = $path;
        $file['folder'] = $folder;

        $result = $this->manager->saveFile($path, $content);

        if ($result === true) {
            $upload = $this->saveToDb($file);

            if ($request->ajax()) {
                $file = $this->manager->fileDetails($upload);
                return Response::json(['success' => true, 'status' => trans('filemanager::filemanager.file_uploaded', ['file' => $fileName]), 'file' => $file]);
            }
            return redirect()
                ->back()
                ->withSuccess(trans('filemanager::filemanager.file_uploaded', ['file' => $fileName]));
        }

        $error = $result ?: trans('filemanager::filemanager.error_uploading_file');
        if ($request->ajax()) {
            return Response::json(['success' => false, 'errors' => [$error]]);
        }
        return redirect()
            ->back()
            ->withErrors([$error]);
    }

    /**
     * Save upload to the database
     *
     * @param $file
     * @param bool $fromDisk
     * @return Uploads
     */
    private function saveToDb($file, bool $fromDisk = false): Uploads
    {
        $upload = new Uploads();
        $upload->filename = $file['name'];
        $upload->folder = $file['folder'];
        $upload->mimeType = $file['type'];
        $upload->size = $file['size'];
        if (is_image($upload->mimeType)) {
            $diskPath = $this->manager->diskFilePath($file['path']);
            try {
                $exif_data = exif_read_data($diskPath, 0, true);
                if (array_key_exists('EXIF', $exif_data) && array_key_exists('DateTimeOriginal', $exif_data['EXIF'])) {
                    $timeTaken = Carbon::createFromFormat('Y:m:d H:i:s', $exif_data['EXIF']['DateTimeOriginal'])->format('Y-m-d H:i:s');
                }
                $dimension = ['width' => $exif_data['COMPUTED']['Width'], 'height' => $exif_data['COMPUTED']['Height']];
            } catch (\Exception $exception){
                // no headers, get the width/height another way
                list($width, $height) = getimagesize($diskPath);
                $dimension = ['width' => $width, 'height' => $height];
            }
            $upload->dimension = $dimension;
        }
        $upload->time_taken = $timeTaken ?? ($fromDisk ? $this->manager->fileModified($file['path']) : Carbon::now());
        $upload->added_by_id = auth()->id();
        $upload->save();

        return $upload;
    }

    /**
     * Delete file in folder from the database
     *
     * @param $fileName
     * @param $folder
     */
    private function delFromDb($fileName, $folder)
    {
        try {
            Uploads::where('filename', $fileName)->where('folder', $folder)->delete();
        } catch (QueryException $e) {
            if($e->errorInfo[0] == 23000){
                return trans('filemanager::filemanager.error_file_delete_in_use');
            }
            return trans('filemanager::filemanager.error_deleting_file');
        }
        return true;
    }
}