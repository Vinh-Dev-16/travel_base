<?php

namespace Vinhdev\Travel\Contracts\Models;

use Vinhdev\Travel\Contracts\Enums\SoftDelete;
use MongoDB\BSON\ObjectId;
use MongoDB\Laravel\Eloquent\Model;

class BaseModel extends Model
{
    protected $primaryKey = '_id';
    public $timestamps = false;

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->_id)) {
                $model->_id = new ObjectId();
            }

            if (empty($model->created_at)) {
                $model->created_at = now()->timestamp;
            }

            if (empty($model->updated_at)) {
                $model->updated_at = now()->timestamp;
            }

            if (empty($model->is_deleted)) {
                $model->is_deleted = SoftDelete::ACTIVE->value;
            }
        });
    }
}