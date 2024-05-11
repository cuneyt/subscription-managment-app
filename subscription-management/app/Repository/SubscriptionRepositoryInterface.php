<?php


namespace App\Repository;


interface SubscriptionRepositoryInterface
{

    public function findBy(int $column, int $key);
    public function create(array $data);
    public function update(int $uid, int $appid);
    public function canceled(int $uid, int $appid);
    public function getExpiredData(String $date);

}
