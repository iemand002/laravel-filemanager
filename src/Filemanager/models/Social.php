<?php

namespace Iemand002\Filemanager\models;

use Illuminate\Database\Eloquent\Model;

class Social extends Model
{
    protected $table;
    public $timestamps = true;

    /**
     * Uploads constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('filemanager.social_table', 'social_logins');
        parent::__construct($attributes);
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}