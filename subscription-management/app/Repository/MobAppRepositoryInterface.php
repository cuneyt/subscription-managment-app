<?php


namespace App\Repository;


interface MobAppRepositoryInterface
{

    public function find(int $uid);
    public function create(array $data);

}
