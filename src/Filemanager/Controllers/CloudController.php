<?php

namespace Iemand002\Filemanager\Controllers;

use App\Http\Controllers\Controller;
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
                return $this->browseOneDrive($request->get('folder'));
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
     * @param Picture $pic
     * @param $size
     * @return int|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function showPicture(Picture $pic, $size)
    {
        switch ($pic->provider) {
            case 'dropbox':
                $social = Social::where('user_id', $pic->added_by_id)->where('provider', 'dropbox')->first();
                return $this->getDropboxPic($pic, $size, $social);
                break;
            case 'graph':
                $social = Social::where('user_id', $pic->added_by_id)->where('provider', 'graph')->first();
                return $this->getOnedrivePic($pic, $size, $social);
                break;
            default;
        }
        return null;
    }

    /**
     * Get info of picture.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function info($pic)
    {
        $addedBy = $pic->addedBy->profile;

        $return = [
            'user' => $addedBy->name,
            'user_slug' => $addedBy->slug,
            'user_pic' => $addedBy->picture,
            'likes' => $pic->likes->count(),
            'liked' => auth()->check() && !is_null(Like::userHasLiked('App\Picture', $pic->id, auth()->id())->first())
        ];
        // mark corresponding unread picture notification as read
        if (auth()->check()) {
            foreach (auth()->user()->unreadNotifications as $notification) {
                if (($notification->type == PictureLiked::class || $notification->type == PictureCommented::class) && $notification->data['picture_id'] == $pic->id) {
                    $notification->markAsRead();
                }
            }
        }

        return Response::json($return);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request, $album, $provider = null)
    {
        $i = 0;
        foreach ($request->input('pics') as $pic) {
            // save picture info
            $picture = new Picture();
            $picture->album_id = $album->id;
            $picture->url = $pic;
            list($width, $height) = explode('x', $request->input('dimensions')[$i]);
            switch ($provider) {
                case 'graph':
                    $dimensions = [
                        'original' => ['width' => (int)$width, 'height' => (int)$height],
                        'thumb' => $this->resize_dimensions(176, 176, $width, $height),
                        'big' => $this->resize_dimensions(800, 800, $width, $height)
                    ];
                    break;
                default:
                    $dimensions = [
                        'original' => ['width' => (int)$width, 'height' => (int)$height],
                        'thumb' => $this->resize_dimensions(640, 480, $width, $height),
                        'big' => $this->resize_dimensions(1024, 768, $width, $height)
                    ];
            }
            $picture->dimensions = $dimensions;
            $picture->time_taken = new Carbon($request->input('time_taken')[$i]);
            $picture->provider = $provider ? $provider : 'dropbox';
            $picture->added_by_id = auth()->id();
            $picture->save();
            $i++;
        }
        // sync user with contributors of album
        $album->contributors()->syncWithoutDetaching([auth()->id()]);

        // notify other contributors that there are new picture(s)
        $contributors = $album->contributors()->where('user_id', '!=', auth()->id())->get();
        foreach ($contributors as $user) {
            foreach ($user->unreadNotifications as $notification) {
                // if notification of album already exist, delete
                if ($notification->type == NewPictures::class && $notification->data['album'] == $album->slug) {
                    $notification->delete();
                }
            }
            $user->notify(new NewPictures($album, $user));
        }

        return redirect(route('album.show', ['album' => $album->slug]))->with('message', 'imported');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        $picture = Picture::find(Hashids::decode($request->picture))->first();
        $album = $picture->album;

        // double check if user is owner of picture
        if ($picture->added_by_id == auth()->id()) {

            $this->removePicture($picture);
            if (count($album->picturesAddedBy(auth()->id())) == 0) {

                return response()->json(['success' => true, 'redirect' => route('album.show', [$album->slug])]);
            }

            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }

    public function delete(Request $request)
    {
        Picture::find($request->input('id'))->delete();

        return redirect()->back();
    }

    public function restore(Request $request)
    {
        Picture::withTrashed()->whereId($request->input('id'))->restore();

        return redirect()->back();
    }


    /**
     * @param $album
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function imports($album)
    {
        return view('picture.imported', compact('album'));
    }

    /**
     * Calculates restricted dimensions with a maximum of $goal_width by $goal_height
     *
     * @param $goal_width
     * @param $goal_height
     * @param $width
     * @param $height
     * @return array
     */
    private function resize_dimensions($goal_width, $goal_height, $width, $height)
    {
        $return = array('width' => $width, 'height' => $height);

        // If the ratio > goal ratio and the width > goal width resize down to goal width
        if ($width / $height > $goal_width / $goal_height && $width > $goal_width) {
            $return['width'] = $goal_width;
            $return['height'] = (int)($goal_width / $width * $height);
        } // Otherwise, if the height > goal, resize down to goal height
        else if ($height > $goal_height) {
            $return['width'] = (int)($goal_height / $height * $width);
            $return['height'] = $goal_height;
        }

        return $return;
    }

    /**
     * @param $picture
     */
    private function removePicture($picture)
    {
        $album = Album::find($picture->album_id)->first();
        $userId = $picture->added_by_id;
        // delete all notifications about picture
        foreach (auth()->user()->notifications as $notification) {
            if (($notification->type == PictureCommented::class || $notification->type == PictureLiked::class) && $notification->data['picture_id'] == $picture->id) {
                $notification->delete();
            }
        }
        // delete all picture likes (forceDelete because of softDelete)
        $picture->likes()->forceDelete();
        // delete all picture comments
        $picture->comments()->delete();
        // delete picture
        $picture->forceDelete();
        // if no pictures left for user, detach from contributors
        if (count($album->picturesAddedBy($userId)) == 0) {
            $album->contributors()->detach([$userId]);
        }
    }

}

?>