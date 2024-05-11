<?php


namespace App\Repository;
use App\Models\Subscription;

class PaymentRepository implements PaymentRepositoryInterface
{


    public function paymentprocress()
    {

    }


    public function find(int $uid)
    {
        return Subscription::where('uid',$uid)->first();
    }

    public function create(array $data)
    {
       return Subscription::create($data);
    }
}
