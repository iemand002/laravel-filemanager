<?php
if (Config::get('filemanager.middleware') != 'none') {
    Route::group(['middleware' => config('filemanager.middleware'), 'prefix' => config('filemanager.prefix')], function () {
        Route::get('/upload', ['as' => 'filemanager.index', 'uses' => '\Iemand002\Filemanager\Controllers\UploadController@index']);
        Route::get('/upload-picker', ['as' => 'filemanager.picker', 'uses' => '\Iemand002\Filemanager\Controllers\UploadController@picker']);
        Route::post('/upload/file', ['as' => 'filemanager.upload-file', 'uses' => '\Iemand002\Filemanager\Controllers\UploadController@uploadFile']);
        Route::delete('/upload/file', ['as' => 'filemanager.delete-file', 'uses' => '\Iemand002\Filemanager\Controllers\UploadController@deleteFile']);
        Route::post('/upload/folder', ['as' => 'filemanager.create-folder', 'uses' => '\Iemand002\Filemanager\Controllers\UploadController@createFolder']);
        Route::delete('/upload/folder', ['as' => 'filemanager.delete-folder', 'uses' => '\Iemand002\Filemanager\Controllers\UploadController@deleteFolder']);
        Route::get('/sync', ['as' => 'filemanager.save-legacy', 'uses' => '\Iemand002\Filemanager\Controllers\UploadController@sync']);
    });
} else {
    Route::group(['prefix' => config('filemanager.prefix')], function () {
        Route::get('/upload', ['as' => 'filemanager.index', 'uses' => '\Iemand002\Filemanager\Controllers\UploadController@index']);
        Route::get('/upload-picker', ['as' => 'filemanager.picker', 'uses' => '\Iemand002\Filemanager\Controllers\UploadController@picker']);
        Route::post('/upload/file', ['as' => 'filemanager.upload-file', 'uses' => '\Iemand002\Filemanager\Controllers\UploadController@uploadFile']);
        Route::delete('/upload/file', ['as' => 'filemanager.delete-file', 'uses' => '\Iemand002\Filemanager\Controllers\UploadController@deleteFile']);
        Route::post('/upload/folder', ['as' => 'filemanager.create-folder', 'uses' => '\Iemand002\Filemanager\Controllers\UploadController@createFolder']);
        Route::delete('/upload/folder', ['as' => 'filemanager.delete-folder', 'uses' => '\Iemand002\Filemanager\Controllers\UploadController@deleteFolder']);
        Route::get('/sync', ['as' => 'filemanager.save-legacy', 'uses' => '\Iemand002\Filemanager\Controllers\UploadController@sync']);
    });
}