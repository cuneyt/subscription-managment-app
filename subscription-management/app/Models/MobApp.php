<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDb\Laravel\Eloquent\Model;


class MobApp extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $table = 'mobapp';
    protected $fillable = ['appid','apptitle','uname','pass'];

   /* protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('indexes', function ($builder) {
            $collection = $builder->getConnection()->getCollection('mobapp');
            $collection->createIndex(['appid' => 1]);
        });

        Schema::create('mobapp', function (Blueprint $collection) {
            $collection->index('appid');
        });
    }*/
}
