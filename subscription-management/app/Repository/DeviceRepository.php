<?php


namespace App\Repository;
use App\Models\RegisterDevice;

class DeviceRepository implements DeviceRepositoryInterface
{

    public function all()
    {
        return RegisterDevice::all();
    }

    public function find($id)
    {
        return RegisterDevice::where('uid', $id)->first();
    }

    public function register(array $data)
    {
        return RegisterDevice::create($data);
    }

    public function findBy(string $column, $id)
    {
        return RegisterDevice::where($column, $id)->first();
    }

}
