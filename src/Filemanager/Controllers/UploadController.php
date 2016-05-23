<?php

namespace Iemand002\Filemanager\Controllers;

use Iemand002\Filemanager\Requests\UploadFileRequest;
use Iemand002\Filemanager\Requests\UploadNewFolderRequest;
use Iemand002\Filemanager\Services\UploadsManager;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;


class UploadController extends Controller
{
    protected $manager;

    public function __construct(UploadsManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Show page of files / subfolders
     */
    public function index(Request $request)
    {
        $folder = $request->get('folder');
        $data = $this->manager->folderInfo($folder);
        $data['active']='filemanager';

        return view('iemand002/filemanager::index', $data);
    }

    /**
     * Show page of files / subfolders
     */
    public function picker(Request $request)
    {
        $folder = $request->get('folder');
        $data = $this->manager->folderInfo($folder);

        return view('iemand002/filemanager::picker', $data);
    }

    /**
     * Create a new folder
     */
    public function createFolder(UploadNewFolderRequest $request)
    {
        $new_folder = $request->get('new_folder');
        $folder = $request->get('folder').'/'.$new_folder;

        $result = $this->manager->createDirectory($folder);

        if ($result === true) {
            return redirect()
                ->back()
                ->withSuccess(trans('filemanager::filemanager.folder_created',['folder'=>$new_folder]));
        }

        $error = $result ? : trans('filemanager::filemanager.error_creating_folder');
        return redirect()
            ->back()
            ->withErrors([$error]);
    }

    /**
     * Delete a file
     */
    public function deleteFile(Request $request)
    {
        $del_file = $request->get('del_file');
        $path = $request->get('folder').'/'.$del_file;

        $result = $this->manager->deleteFile($path);

        if ($result === true) {
            return redirect()
                ->back()
                ->withSuccess(trans('filemanager::filemanager.file_deleted',['file'=>$del_file]));
        }

        $error = $result ? : trans('filemanager::filemanager.error_deleting_file');
        return redirect()
            ->back()
            ->withErrors([$error]);
    }

    /**
     * Delete a folder
     */
    public function deleteFolder(Request $request)
    {
        $del_folder = $request->get('del_folder');
        $folder = $request->get('folder').'/'.$del_folder;

        $result = $this->manager->deleteDirectory($folder);

        if ($result === true) {
            return redirect()
                ->back()
                ->withSuccess(trans('filemanager::filemanager.folder_deleted',['folder'=>$del_folder]));
        }

        $error = $result ? : trans('filemanager::filemanager.error_deleting_folder');
        return redirect()
            ->back()
            ->withErrors([$error]);
    }

    /**
     * Upload new file
     */
    public function uploadFile(UploadFileRequest $request)
    {
        $file = $_FILES['file'];
        $fileName = $request->get('file_name');
        $fileName = $fileName ?: $file['name'];
        $path = str_finish($request->get('folder'), '/') . $fileName;
        $content = File::get($file['tmp_name']);

        $result = $this->manager->saveFile($path, $content);

        if ($result === true) {
            if($request->ajax()){
                $file=$this->manager->fileDetails($path);
                return Response::json(['success' => true,'status'=>trans('filemanager::filemanager.file_uploaded',['file'=>$fileName]),'file'=>$file]);
            }
            return redirect()
                ->back()
                ->withSuccess(trans('filemanager::filemanager.file_uploaded',['file'=>$fileName]));
        }

        $error = $result ? : trans('filemanager::filemanager.error_uploading_file');
        if($request->ajax()){
            return Response::json(['success' => false,'errors'=>[$error]]);
        }
        return redirect()
            ->back()
            ->withErrors([$error]);
    }
}
