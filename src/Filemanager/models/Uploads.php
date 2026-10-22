<?php

namespace Iemand002\Filemanager\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Uploads extends Model
{
    protected $table;
    public $timestamps = true;
    protected $casts = [
        'dimension' => 'object',
        'time_taken' => 'datetime'
    ];

    /**
     * Uploads constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('filemanager.table', 'uploads');
        parent::__construct($attributes);
    }

    public function scopeWidth()
    {
        return $this->dimension->width ?? 0;
    }

    public function scopeHeight()
    {
        return $this->dimension->height ?? 0;
    }

    public function transforms(): HasMany
    {
        return $this->hasMany('Iemand002\Filemanager\models\Transforms', 'upload_id');
    }
}
