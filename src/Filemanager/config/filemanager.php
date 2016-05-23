<?php
return [
    // Where to leave the uploads?
    'uploads' => [
        'storage' => 'public',
        'webpath' => '/uploads', // Change in filesystem.php the root to public_path('uploads')
    ],

    // routes
    'middleware' => array('web'), // if you don't want to use a middleware make it 'none'
    'prefix' => 'admin',

    // Page sections
    'extend_layout' => [
        'normal' => 'layouts.app',
        'picker' => 'layouts.blank',
    ],
    'javascript_section' => 'js',
    'css_section' => 'css',
    'pagetitle_section'=>'pagetitle',
    'content_section'=>'content',
    'include_container' => 'normal', // options: normal | fluid | none
    
    // Picker: trigger onchange?
    'on_change'=>false,

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
];