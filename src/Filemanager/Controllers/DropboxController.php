<?php

namespace Iemand002\Filemanager\Controllers;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Iemand002\Filemanager\models\Social;
use Illuminate\Http\Request;
use \Intervention\Image\Facades\Image;

class DropboxController extends Controller {

    /**
     * @param $album
     * @param string $folder_name
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    function browseDropbox(Request $request)
    {
        $folder_split = explode('/', $request->get('folder'));

        $folder[0] = ($folder_split[0] != "" ? "/" . $folder_split[0] : "");
        for ($i = 0; $i < sizeof($folder_split) - 1; $i++) {
            $folder[] = $folder[$i] . '/' . $folder_split[$i + 1];
        }

        $social = Social::where('user_id', auth()->id())->where('provider', 'dropbox')->first();

        $body = ["path" => $folder[sizeof($folder) - 1], 'include_media_info' => true];

        if ($social) {

            $request = new \GuzzleHttp\Psr7\Request(
                'post',
                'https://api.dropboxapi.com/2/files/list_folder',
                [
                    'Authorization' => 'Bearer ' . $social->token,
                    'Content-Type' => 'application/json'
                ],
                \GuzzleHttp\json_encode($body)
            );
            $client = new Client();

            try {
                $response = $client->send($request);
                $data = \GuzzleHttp\json_decode($response->getBody()->getContents());

                return view('iemand002/filemanager::pickerDropbox', compact('data', 'folder', 'folder_split'));

            } catch (BadResponseException $e) {

                dd($e);
            }
        }
        return redirect()->route('social.redirect', ['provider' => 'dropbox', 'redirect' => route('filemanager.pickerDropbox')]);
    }



    /**
     * @param $id
     * @return int|mixed
     */
    function getDropboxPicture($id)
    {
        $social = Social::where('user_id', auth()->id())->where('provider', 'dropbox')->first();
        return $this->getDropboxPic($id, 'big', $social);
    }

    /**
     * @param Picture $pic
     * @param $size
     * @param $social
     * @return int|mixed
     */
    private function getDropboxPic($pic, $size, $social)
    {
        switch ($size) {
            case 'icon':
                $size = "w64h64";
                break;
            case 'big':
                $size = "w1024h768";
                break;
            case 'thumb':
            default:
                $size = "w640h480";
                break;
        }
        if ($social) {
            $body = ["path" => $pic, "size" => [".tag" => $size]];

            $request = new \GuzzleHttp\Psr7\Request(
                'post',
                'https://content.dropboxapi.com/2/files/get_thumbnail',
                [
                    'Authorization' => 'Bearer ' . $social->token,
                    'User-Agent' => 'api-explorer-client',
                    'Dropbox-API-Arg' => json_encode($body)
                ]
            );
            $client = new Client();

            try {
                $response = $client->send($request);
                return $response->getBody()->getContents();
                $img = Image::make(base64_encode($response->getBody()->getContents()));
                return $img->response();

            } catch (BadResponseException $e) {

                if ($e->getCode() == 409) {
//                    $this->removePicture($pic);
//                    return 'removed';
                }
                return ($e->getCode());
            }
        }
    }
}