<?php

namespace Iemand002\Filemanager;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Iemand002\Filemanager\FilemanagerBuilder
 */
class FilemanagerFacade extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'filemanager';
    }
}