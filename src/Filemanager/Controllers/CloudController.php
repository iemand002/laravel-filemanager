<?php

namespace Iemand002\Filemanager\Controllers;

use App\Http\Controllers\Controller;
use Iemand002\Filemanager\models\Social;
use Iemand002\Filemanager\models\Uploads;
use Iemand002\Filemanager\Traits\DropboxTrait;
use Iemand002\Filemanager\Traits\OnedriveTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CloudController extends Controller
{
    use DropboxTrait, OnedriveTrait;

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
    function showPicture($provider, $folderFile, $size = null)
    {
        $file = substr($folderFile, strrpos($folderFile, '/') + 1);
        $folder = str_replace(' ','%20',substr($folderFile,0,strrpos($folderFile, '/') + 1));
        $pic = Uploads::where('folder',$folder)->where('filename',$file)->first();
        switch ($provider) {
            case 'dropbox':
                $social = Social::where('user_id', $pic->added_by_id)->where('provider', 'dropbox')->first();
                return $this->getDropboxPic($pic, $size, $social);
                break;
            case 'onedrive':
                $social = Social::where('user_id', $pic->added_by_id)->where('provider', 'graph')->first();
                return $this->getOnedrivePic($pic, $size, $social);
                break;
            default;
        }
        return null;
    }

    /**
     * Store the selected files
     *
     * @param Request $request
     * @return false|string
     */
    public function store(Request $request)
    {
        $uploads = [];
        foreach ($request->input('files') as $file) {
            if (!Uploads::where('key',$file["fileId"])->first()) {
                // save upload info
                $upload = new Uploads();
                $upload->filename = $file["fileName"];
                $upload->folder = $request->input('folder');
                $upload->key = $file["fileId"];
                $upload->time_taken = new Carbon($file["fileDate"]);
                $upload->provider = $request->input('cloud');
                $upload->added_by_id = auth()->id();
                $upload->save();
                $uploads[] = $upload;
            }
        }

        return json_encode($uploads);
    }

}

?>