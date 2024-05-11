<?php
namespace App\Repository;
use App\Models\Subscription;

class SubscriptionRepository implements SubscriptionRepositoryInterface
{

    public function findBy(int $uid, int $appid )
    {
        return Subscription::where('uid',$uid)->where("appid",$appid)->first();
    }

    public function create(array $data)
    {
       return Subscription::create($data);
    }

    public function update(int $uid, int $appid)
    {
        $after10min= date("Y-m-d H:i:s", strtotime("+10 minutes"));
        return Subscription::where('uid', $uid)
            ->where('appid', $appid)
            ->update(['expired_date' => $after10min, 'substatus' => true]);
    }

    public function canceled(int $uid, int $appid)
    {
        //$after10min= date("Y-m-d H:i:s", strtotime("+10 minutes"));
        $after10min= date("Y-m-d H:i:s");
        return Subscription::where('uid', $uid)
            ->where('appid', $appid)
            ->update(['substatus' => false]);
    }

    public function getExpiredData(String $date)
    {
        return Subscription::where('expired_date','<=',$date)->get();
    }
}
