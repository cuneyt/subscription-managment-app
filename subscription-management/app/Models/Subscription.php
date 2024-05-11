<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDb\Laravel\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $table = 'subscription';
    protected $fillable = ['substatus','expired_date','appid','uid'];

    /*protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('indexes', function ($builder) {
            $collection = $builder->getConnection()->getCollection('subscription');
            $collection->createIndex(['uid' => 1, 'appid' => 1]);
            $collection->createIndex(['expired_date' => 1]);
            $collection->createIndex(['substatus' => 1]);
        });
    }*/
}
