<?php


namespace App\Repository;
use App\Models\MobApp;

class MobAppRepository implements MobAppRepositoryInterface
{

    public function find(int $appid)
    {
        return MobApp::where('appid',$appid)->first();
    }

    public function create(array $data)
    {
       return MobApp::create($data);
    }
}
