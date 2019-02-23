<?php

namespace Iemand002\Filemanager\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

trait OnedriveTrait
{
    /**
     * @param $album
     * @param string $folders
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function browseOneDrive($album, $folders = "")
    {
        $pictureIds = [];
        foreach ($album->picturesAddedByWithTrashed(auth()->id()) as $pic) {
            $pictureIds[] = $pic->url;
        }
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
                    return view('picture.import.onedrive', compact('data', 'folders', 'album', 'pictureIds'));
                } else {
                    // folder is empty, redirect to previous folder
                    array_pop($folders);
                    $folders = implode("-", $folders);
                    \Session::flash('info', 'Folder is empty, redirect to previous folder');
                    return redirect(route('picture.import', ['provider' => 'onedrive', 'album' => $album->slug, 'folders' => $folders]));
                }

            } catch (BadResponseException $e) {

                if ($e->getCode() == 401) {
                    if ($this->refreshToken($social, route('picture.import', ['provider' => 'onedrive', $album->slug]))) {
                        return $this->browseOneDrive($album, $folder = "");
                    }
                }
                dd($e);
            }
        }
        return redirect()->route('social.redirect', ['provider' => 'graph', 'redirect' => route('picture.import', ['provider' => 'onedrive', $album->slug])]);
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
        $pic = new Picture();
        $pic->url = $id;
        $social = Social::where('user_id', auth()->id())->where('provider', 'graph')->first();
        return $this->getOnedrivePic($pic, 'big', $social);
    }

    /**
     * TODO: handle deleted onedrive pictures
     * @param Picture $pic
     * @param $size
     * @param $social
     * @return int|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getOnedrivePic(Picture $pic, $size, $social)
    {
        $type = 'default';
        $url = 'https://graph.microsoft.com/v1.0/me/drive/items/' . $pic->url . '/thumbnails';
        switch ($size) {
            case 'icon':
                $size = "smallSquare";
                break;
            case 'big':
                $size = "large";
                break;
            case 'thumb':
            default:
                $type = 'custom';
                $size = "640x480";
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
                    $img = Image::make($data->value[0]->c640x480->url);
                } else {
                    $img = Image::make($data->url);
                }
                return $img->response();

            } catch (BadResponseException $e) {

                if ($e->getCode() == 401) {
                    if ($this->refreshToken($social, route('picture.getPicture', ['onedrive', $pic->url]))) {
                        return $this->getOnedrivePic($pic, $size, $social);
                    }
                }
                if ($e->getCode() == 404) {
                    $this->removePicture($pic);
                    return 'removed';
                }
                return ($e->getCode());
            }
        }
    }
}