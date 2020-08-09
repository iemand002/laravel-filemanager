<?php

use Iemand002\Filemanager\models\Social;
use Illuminate\Support\Str;

/**
 * Return sizes readable by humans
 * @param $bytes
 * @param int $decimals
 * @return string
 */
function human_filesize($bytes, $decimals = 2)
{
    $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB'];
    $factor = floor((strlen($bytes) - 1) / 3);

    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) .
        @$size[$factor];
}

/**
 * Is the mime type an image
 * @param $mimeType
 * @return bool
 */
function is_image($mimeType)
{
    return Str::startsWith($mimeType, 'image/');
}

function fileMimeType($path){
    $mimeDetect = new \Dflydev\ApacheMimeTypes\PhpRepository();
    return $mimeDetect->findType(
        pathinfo(strtolower($path), PATHINFO_EXTENSION)
    );
}

function is_dropbox_configured(){
    if(config('services.dropbox.client_id') && config('services.dropbox.client_secret') && config('services.dropbox.redirect')){
        return true;
    }
    return false;
}

function is_dropbox_loggedIn(){
    if(is_dropbox_configured() && auth()->check()){
        $social = Social::where('user_id', auth()->id())->where('provider', 'dropbox')->first();
        if($social) {
            return true;
        }
    }
    return false;
}

function is_onedrive_configured(){
    if(config('services.graph.client_id') && config('services.graph.client_secret') && config('services.graph.redirect')){
        return true;
    }
    return false;
}

function is_onedrive_loggedIn(){
    if(is_onedrive_configured() && auth()->check()){
        $social = Social::where('user_id', auth()->id())->where('provider', 'graph')->first();
        if($social) {
            return true;
        }
    }
    return false;
}