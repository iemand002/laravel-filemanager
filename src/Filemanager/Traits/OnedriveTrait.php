<?php

namespace Iemand002\Filemanager\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Iemand002\Filemanager\models\Social;
use Iemand002\Filemanager\models\Uploads;
use Intervention\Image\Facades\Image;

trait OnedriveTrait
{
    /**
     * @param string $folders
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function browseOneDrive($folders = "")
    {
        $folders = explode('-', $folders);

        $social = Social::where('user_id', auth()->id())->where('provider', 'graph')->first();

        if ($social) {

            $url = 'https://graph.microsoft.com/v1.0/me/drive/root/children';
            if (end($folders)) {
                $url = 'https://graph.microsoft.com/v1.0/me/drive/items/' . end($folders) . '/children';
            }

            $request = new \GuzzleHttp\Psr7\Request(
                'get',
                $url,
                [
                    'Authorization' => 'Bearer ' . $social->token,
                    'Content-Type' => 'application/json'
                ]
            );
            $client = new Client();

            try {
                $response = $client->send($request);
                $data = \GuzzleHttp\json_decode($response->getBody()->getContents());

                if ($data->value) {
                    return view('iemand002/filemanager::pickerOnedrive', compact('data', 'folders'));
                } else {
                    // folder is empty, redirect to previous folder
                    array_pop($folders);
                    $folders = implode("-", $folders);
                    \Session::flash('info', 'Folder is empty, redirect to previous folder');
                    return redirect(route('filemanager.pickerCloud', ['provider' => 'onedrive', 'folders' => $folders]));
                }

            } catch (BadResponseException $e) {

                if ($e->getCode() == 401) {
                    if ($this->refreshToken($social, route('filemanager.pickerCloud', ['provider' => 'onedrive']))) {
                        return $this->browseOneDrive($folder = "");
                    }
                }
                dd($e);
            }
        }
        return redirect()->route('social.redirect', ['provider' => 'graph', 'redirect' => route('filemanager.pickerCloud', ['provider' => 'onedrive'])]);
    }

    /**
     * @param $social
     * @param $redirect
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function refreshToken($social, $redirect)
    {
        $body = [
            "client_id" => env('GRAPH_KEY'),
            'scope' => 'openid Files.ReadWrite Files.ReadWrite.All Sites.ReadWrite.All offline_access',
            'refresh_token' => $social->refresh,
            'redirect_uri' => $redirect,
            'grant_type' => 'refresh_token',
            'client_secret' => env('GRAPH_SECRET')
        ];

        $request = new \GuzzleHttp\Psr7\Request(
            'post',
            'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            http_build_query($body)
        );
        $client = new Client();

        try {
            $response = $client->send($request);
            $data = \GuzzleHttp\json_decode($response->getBody()->getContents());
            $social->token = $data->access_token;
            $social->refresh = $data->refresh_token;
            $social->expires = $data->expires_in;
            $social->save();

            return true;

        } catch (BadResponseException $e) {
            dd($e);
            return false;
        }
    }

    /**
     * @param $id
     * @return int|mixed
     */
    function getOnedrivePicture($id)
    {
        $pic = new Uploads();
        $pic->key = $id;
        $social = Social::where('user_id', auth()->id())->where('provider', 'graph')->first();
        return $this->getOnedrivePic($pic, 'big', $social);
    }

    /**
     * @param Uploads $pic
     * @param $size
     * @param $social
     * @return int|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getOnedrivePic(Uploads $pic, $size, $social)
    {
        $type = 'default';
        $url = 'https://graph.microsoft.com/v1.0/me/drive/items/' . $pic->key . '/thumbnails';
        switch ($size) {
            case 'icon':
                $size = "smallSquare";
                break;
            case 'big':
                $size = "large";
                break;
            case 'thumb':
                $type = 'custom';
                $size = "640x480";
                break;
            default:
                $type = 'custom';
                $size = $this->calculateSize($size);
                break;
        }
        if ($social) {

            if ($type == 'custom') {
                $url .= '?select=c' . $size;
            } else {
                $url .= '/0/' . $size;
            }

            $request = new \GuzzleHttp\Psr7\Request(
                'get',
                $url,
                [
                    'Authorization' => 'Bearer ' . $social->token,
                    'Content-type' => 'application/json'
                ]
            );
            $client = new Client();

            try {
                $response = $client->send($request);
                $data = \GuzzleHttp\json_decode($response->getBody()->getContents());
                if ($type == 'custom') {
                    $img = Image::make(((array)$data->value[0])['c' . $size]->url);
                } else {
                    $img = Image::make($data->url);
                }
                return $img->response();

            } catch (BadResponseException $e) {

                if ($e->getCode() == 401) {
                    if ($this->refreshToken($social, route('filemanager.showPicture', ['onedrive', $pic->folder . $pic->filename]))) {
                        return $this->getOnedrivePic($pic, $size, $social);
                    }
                }
                if ($e->getCode() == 404) {
                    // TODO: handle deleted items
                    return 'removed';
                }
                return ($e->getCode());
            }
        }
    }

    private function calculateSize($transformHandle)
    {
        $default = config('filemanager.cloud_default_transform');
        list($width, $height, $squared, $quality) = $default;

        if ($transformHandle == null) {
            return $width . 'x' . $height;
        }
        $transforms = config('filemanager.transforms');
        $transform = $transforms[$transformHandle];

        if (empty($transform) || !is_array($transform)) {
            return $width . 'x' . $height;
        }
        list($width, $height, $squared, $quality) = $transform;

        return $width . 'x' . $height;
    }
}