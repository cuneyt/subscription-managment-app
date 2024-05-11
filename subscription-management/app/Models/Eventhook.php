<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDb\Laravel\Eloquent\Model;

class Eventhook extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $table = 'eventhook';
    protected $fillable = ['url'];
}
