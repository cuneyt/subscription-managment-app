<?php


namespace App\Repository;

interface DeviceRepositoryInterface
{

    public function all();
    public function find($id);
    public function findBy(string $column, $id);
    public function register(array $data);
}
