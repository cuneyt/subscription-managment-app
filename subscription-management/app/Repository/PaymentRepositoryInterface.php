<?php


namespace App\Repository;


interface PaymentRepositoryInterface
{

    public function paymentprocress();

    public function find(int $uid);
    public function create(array $data);

}
