<?php
return [
    // Where to leave the uploads?
    'uploads' => [
        'storage' => env('FILEMANAGER_STORAGE','default'), // filesystem 'default' or 'cloud'
        'temp' => 'temp', // temporary upload folder for image transforms
    ],

    // Table
    'table' => 'uploads', // Table to store the uploads
    'social_table' => 'social_logins', // Table to store the social logins
    'users_table' => 'users', // Table where the users are stored, usually this is the default 'users'

    /*
     * Library used to manipulate image.
     * Options: gd (default), imagick, gmagick
     */
    'library' => env('IMAGEUPLOAD_LIBRARY', 'gd'),

    /*
     * Quality for JPEG type.
     * Scale: 1-100;
     */
    'quality' => env('IMAGEUPLOAD_QUALITY', 90),

    // routes
    'middleware' => [], // web middleware by default
    'prefix' => 'admin',

    // Page sections
    'extend_layout' => [
        'normal' => 'layouts.app',
        'picker' => 'layouts.blank',
    ],
    'javascript_section' => 'js',
    'css_section' => 'css',
    'pagetitle_section' => 'pagetitle',
    'content_section' => 'content',
    'include_container' => 'normal', // options: normal | fluid | none

    // JQuery datatables from https://datatables.net/
    'jquery_datatables' => [
        'use' => true,
        'cdn' => true, // If false you need to add them yourself, otherwise they are added to the javascript_section and css_section
    ],

    // Show alert messages
    'alert_messages' => [
        'normal' => true,
        'picker' => true,
    ],

    /*
   * Sizes, used to crop and create multiple size.
   *
   * array(width, height, squared, quality)
   *  if square set to TRUE, image will be in square
   * if quality set to NULL, the default setting from above will be used
   */
    'transforms' => [
        'square50' => [50, 50, true, 100],
        'square100' => [100, 100, true, null],
        'square200' => [200, 200, true, null],
        'square400' => [400, 400, true, null],

        'size50' => [50, 50, false, null],
        'size100' => [100, 100, false, null],
        'size200' => [200, 200, false, null],
        'size400' => [400, 400, false, null],
    ],
];