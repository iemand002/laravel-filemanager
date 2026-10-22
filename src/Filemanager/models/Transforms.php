<?php

namespace Iemand002\Filemanager\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transforms extends Model
{
    protected $table;
    public $timestamps = true;
    protected $casts = [
        'dimension' => 'object',
    ];

    /**
     * Uploads constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('filemanager.transforms_table', 'uploads_transforms');
        parent::__construct($attributes);
    }

    public function scopeWidth(){
        return $this->dimension->width ?? 0;
    }

    public function scopeHeight(){
        return $this->dimension->height ?? 0;
    }

    public function uploads(): BelongsTo
    {
        return $this->belongsTo('Iemand002\Filemanager\models\Uploads');
    }
}