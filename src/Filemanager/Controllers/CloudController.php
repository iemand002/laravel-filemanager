<?php

namespace Iemand002\Filemanager\Controllers;

use App\Http\Controllers\Controller;
use Iemand002\Filemanager\models\Social;
use Iemand002\Filemanager\models\Uploads;
use Iemand002\Filemanager\Traits\DropboxHelperTrait;
use Iemand002\Filemanager\Traits\DropboxTrait;
use Iemand002\Filemanager\Traits\OnedriveTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CloudController extends Controller
{
    use DropboxTrait, DropboxHelperTrait, OnedriveTrait;

    /**
     * @param $provider
     * @param $album
     * @param string $folder_name
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function browseProvider(Request $request, $provider)
    {
        switch ($provider) {
            case 'dropbox':
                return $this->browseDropbox($request->get('folder'));
                break;
            case 'onedrive':
                return $this->browseOneDrive($request->get('folders'));
                break;
        }
        return route(back());
    }

    /**
     * @param $provider
     * @param $id
     * @return int|mixed
     */
    function getPicture($provider, $id)
    {
        switch ($provider) {
            case 'dropbox':
                return $this->getDropboxPicture($id);
                break;
            case 'onedrive':
                return $this->getOnedrivePicture($id);
                break;
        }
        return null;
    }

    /**
     * @param $provider
     * @param $folderFile
     * @param $size
     * @return int|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function showPicture($provider, $folderFile)
    {
        if (substr($folderFile, 0, 1) == '_') {
            $size = substr(strstr($folderFile, '/', true), 1);
            $file = substr($folderFile, strrpos($folderFile, '/') + 1);
            $folder = str_replace(' ', '%20', substr(strstr($folderFile, '/'), 1, strrpos(strstr($folderFile, '/'), '/')));

        } else {
            $size = null;
            $file = substr($folderFile, strrpos($folderFile, '/') + 1);
            $folder = str_replace(' ', '%20', substr($folderFile, 0, strrpos($folderFile, '/') + 1));
        }
        $pic = Uploads::where('folder', $folder)->where('filename', $file)->first();
        switch ($provider) {
            case 'dropbox':
                $social = Social::where('user_id', $pic->added_by_id)->where('provider', 'dropbox')->first();
                return $this->getDropboxPic($pic, $size, $social);
            case 'onedrive':
                $social = Social::where('user_id', $pic->added_by_id)->where('provider', 'graph')->first();
                return $this->getOnedrivePic($pic, $size, $social);
            default;
        }
        return null;
    }

    /**
     * Store the selected files
     *
     * @param Request $request
     * @return array
     */
    public function store(Request $request)
    {
        $uploads = [];
        foreach ($request->input('files') as $file) {
            $upload = Uploads::where('key', $file["fileId"])->first();
            if (!$upload) {
                $dimension = explode('x', $file['fileDimension']);
                // save upload info
                $upload = new Uploads();
                $upload->filename = $file["fileName"];
                $upload->folder = $request->input('folder');
                $upload->mimeType = $file['fileMimeType'];
                $upload->key = $file["fileId"];
                $upload->dimension = $file['fileDimension'] ? ['width' => (int)$dimension[0], 'height' => (int)$dimension[1]] : null;
                $upload->time_taken = new Carbon($file["fileDate"]);
                $upload->provider = $request->input('cloud');
                $upload->added_by_id = auth()->id();
                $upload->save();
            }
            $uploads[] = $upload;
        }

        return ($uploads);
    }

}

?>