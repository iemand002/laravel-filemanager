<?php

namespace Iemand002\Filemanager\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use Iemand002\Filemanager\models\Social;
use Iemand002\Filemanager\models\Uploads;

trait DropboxHelperTrait
{
    function calculateDropboxTransform($upload, $transformHandle)
    {
        $default = config('filemanager.cloud_default_transform');
        list($width, $height, $squared, $quality) = $default;

        if ($transformHandle == null) {
            return ['width'=>$width, 'height'=>$height];
        }
        $transforms = config('filemanager.transforms');
        $transform = $transforms[$transformHandle];

        if (empty($transform) || !is_array($transform)) {
            return ['width'=>$width, 'height'=>$height];
        }
        list($width, $height, $squared, $quality) = $transform;
        if ($squared) {
            $closest = $this->getClosest($width, [32, 64, 128, 256]);
            return ['width'=>$closest, 'height'=>$closest];
        } else {
            $optionsWidth = [480, 640, 960, 1024, 2048];
            $optionsHeight = [320, 480, 640, 768, 1536];

            // If the ratio > goal ratio and the width > goal width resize down to goal width
            if ($upload->dimension->width / $upload->dimension->height > $width / $height && $upload->dimension->width > $width) {
                list($width,$index) = $this->getClosest($width, $optionsWidth);
                $height = $optionsHeight[$index];
            } // Otherwise, if the height > goal, resize down to goal height
            else if ($upload->dimension->height > $height) {
                list($height,$index) = $this->getClosest($height, $optionsHeight);
                $width = $optionsWidth[$index];
            }
        }

        return ['width'=>$width, 'height'=>$height];
    }

    private function getClosest($search, $arr)
    {
        $closest = null;
        foreach ($arr as $i => $item) {
            if ($closest === null || abs($search - $closest) > abs($item - $search)) {
                $closest = $item;
                $index = $i;
            }
        }
        return [$closest, $index];
    }
}