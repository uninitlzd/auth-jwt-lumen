<?php
/**
 * Created by PhpStorm.
 * User: Edo
 * Date: 22/01/2019
 * Time: 16:13
 */

namespace App\Traits;


use Webpatser\Uuid\Uuid;

trait UseUUID
{
    /**
     * Boot function from laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::generate()->string;
        });
    }
}