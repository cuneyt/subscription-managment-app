<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDb\Laravel\Eloquent\Model;

class RegisterDevice extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $table = 'device';

    protected $fillable = ['uid','AppId','os','language','client-token'];

    /*protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('indexes', function ($builder) {
            $collection = $builder->getConnection()->getCollection('device');
            $collection->createIndex(['client-token' => 1]);
            $collection->createIndex(['uid' => 1]);
            $collection->createIndex(['AppId' => 1]);
        });
    }*/
}
