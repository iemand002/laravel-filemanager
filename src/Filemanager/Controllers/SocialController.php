<?php

namespace Iemand002\Filemanager\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Iemand002\Filemanager\models\Social;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{

    public function getSocialRedirect($provider)
    {
        $providerKey = Config::get('services.' . $provider);

        if (empty($providerKey)) {

            return redirect()->back()
                ->withErrors([trans('filemanager::filemanager.provider_not_found', ['provider' => $provider])]);

        }

        if (isset($_GET['redirect'])) {
            session()->flash('redirect', $_GET['redirect']);
        }

        if ($provider == 'graph') {
            return Socialite::driver($provider)->scopes(['openid', 'Files.ReadWrite', 'Files.ReadWrite.All', 'Sites.ReadWrite.All', 'offline_access'])->redirect();
        }

        return Socialite::driver($provider)->redirect();
    }

    public function getSocialHandle(Request $request, $provider)
    {

        if ($request->input('denied') != '') {
            return redirect()->to('/login')
                ->with('status', 'danger')
                ->with('message', 'You did not share your profile data with our social app.');
        }

        $user = Socialite::driver($provider)->user();
        $socialUser = null;

        if (auth()->check()) {
            //if user is already logged in, we want to connect an social
            $userCheck = auth()->user();
        } else {
            //Check is this email present
            $userCheck = User::where('email', $user->email)->first();
        }

        // check if social account already exists
        $sameSocialId = Social::where('social_id', '=', $user->id)
            ->where('provider', '=', $provider)
            ->first();

        $email = $user->email;

        if (!$user->email) {
            $email = 'missing' . Str::random(10);
        }

        if (!empty($userCheck)) {
            // if user exists or is logged in
            $socialUser = $userCheck;
            if (empty($sameSocialId)) {
                // save social login data if not exists
                $socialData = new Social;
                $socialData->social_id = $user->id;
                $socialData->provider = $provider;
                $socialData->token = $user->token;
                if ($provider == 'graph') {
                    $socialData->refresh = $user->refreshToken;
                    $socialData->expires = $user->expiresIn;
                }
                $socialUser->socials()->save($socialData);
            } else {
                if ($provider == 'graph') {
                    $sameSocialId->token = $user->token;
                    $sameSocialId->refresh = $user->refreshToken;
                    $sameSocialId->expires = $user->expiresIn;
                    $sameSocialId->save();
                }
            }

        } else {

            if (empty($sameSocialId)) {

                //There is no combination of this social id and provider, so create new one
                $newSocialUser = new User;
                $newSocialUser->email = $email;

                $newSocialUser->password = bcrypt(Str::random(16));
                $newSocialUser->save();

                $socialData = new Social;
                $socialData->social_id = $user->id;
                $socialData->provider = $provider;
                $socialData->token = $user->token;
                if ($provider == 'graph') {
                    $socialData->refresh = $user->refreshToken;
                    $socialData->expires = $user->expiresIn;
                }
                $newSocialUser->socials()->save($socialData);

                $socialUser = $newSocialUser;

            } else {

                //Load this existing social user
                if ($provider == 'graph') {
                    $sameSocialId->token = $user->token;
                    $sameSocialId->refresh = $user->refreshToken;
                    $sameSocialId->expires = $user->expiresIn;
                    $sameSocialId->save();
                }
                $socialUser = $sameSocialId->user;

            }

        }

        if (!auth()->check()) {
            // if not logged in, log in the (new) user
            auth()->login($socialUser, true);
        }

        if (session('redirect') != null) {
            // if redirect is given
            return redirect(session('redirect'));
        }

        return redirect()->back()->withSuccess(trans('filemanager::filemanager.logged_in_social_provider', ['provider' => $provider]));

    }

}

?>