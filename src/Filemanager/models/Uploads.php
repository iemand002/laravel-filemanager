<?php

namespace Iemand002\Filemanager\models;

use Illuminate\Database\Eloquent\Model;

class Uploads extends Model
{
    protected $table;
    public $timestamps = true;

    public function __construct(array $attributes = [])
    {
        $this->table = config('filemanager.table', 'image_uploads');
        parent::__construct($attributes);
    }
}