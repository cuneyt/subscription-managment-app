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
        $after60min= date("Y-m-d H:i:s", strtotime("+240 minutes"));
        return Subscription::where('uid', $uid)
            ->where('appid', $appid)
            ->update(['expired_date' => $after60min, 'substatus' => true]);
    }

    public function canceled(int $uid, int $appid)
    {
        //$after10min= date("Y-m-d H:i:s", strtotime("+10 minutes"));
        $after10min= date("Y-m-d H:i:s");
        return Subscription::where('uid', $uid)
            ->where('appid', $appid)
            ->update(['substatus' => false]);
    }

    public function getExpiredData()
    {
        $dateafter1min = date("Y-m-d H:i:s", strtotime("-1 minutes"));
        return Subscription::where('expired_date','<=',$dateafter1min)->get();
    }
}
