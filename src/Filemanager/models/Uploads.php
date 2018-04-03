<?php

namespace Iemand002\Filemanager\models;

use Illuminate\Database\Eloquent\Model;

class Uploads extends Model
{
    protected $table;
    public $timestamps = true;

    /**
     * Uploads constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('filemanager.table', 'image_uploads');
        parent::__construct($attributes);
    }
}