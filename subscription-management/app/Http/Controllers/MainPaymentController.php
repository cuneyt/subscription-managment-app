<?php


namespace App\Http\Controllers;
use App\Events\SubscriptionStatusChanged;
use App\Repository\DeviceRepository;
use App\Repository\MobAppRepository;
use App\Repository\SubscriptionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class MainPaymentController
{
    protected $registerDevice;
    protected $application;
    protected $subscription;

    public function __construct(DeviceRepository $registerDevice, MobAppRepository $application, SubscriptionRepository $subscription)
    {
        $this->registerDevice = $registerDevice;
        $this->application = $application;
        $this->subscription = $subscription;
    }

    public function paymentprocessrs(Request $data)
    {
        return $this->handlePayment($data, "started");
    }

    public function renewed(Request $data)
    {
        return $this->handlePayment($data, "renewed");
    }

    private function handlePayment(Request $data, string $event)
    {
        $validator = Validator::make($data->all(), [
            'client-token' => 'required|string',
            'receipt' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => false, "message" => "Doğru isteklerle ulaşılamadı!"], 200);
        }

        $receipt = hash('sha256', $data['receipt'] . rand()) . rand(0, 100);
        $clienttoken = $data['client-token'];

        $uid = $this->getClientUid($clienttoken);
        if (!$uid) {
            return response()->json(["status" => false, "message" => "Client token'a ait veri bulunamadı"], 400);
        }

        if ($event == "started" && $this->checkRedisSubStatus($clienttoken)[0]) {
            return response()->json(["status" => false, "message" => "Zaten aboneliğiniz mevcut"], 200);
        }

        $devicedata = $this->registerDevice->findBy("client-token", $clienttoken);
        $appid = $devicedata->AppId;
        $uid = $devicedata->uid;

        $this->storeClientTokenInRedis($uid, $clienttoken);

        $appdata = $this->application->find($appid);
        $appclient = $appdata->uname;
        $appsecret = $appdata->pass;

        $url = 'http://localhost:8181/api/googleverification';
        $response = Http::withBasicAuth($appclient, $appsecret)->post($url, ['receipt' => $receipt, 'app' => $appid]);
        $json = json_decode($response->getBody()->getContents());

        if ($json->status) {
            event(new SubscriptionStatusChanged($appid, $uid, $event));
            $message = $event == "started" ? "Aboneliğiniz Başladı" : "Aboneliğiniz uzatıldı";
            return response()->json(["status" => true, "message" => $message]);
        } else {
            return response()->json(["status" => false, "message" => "Giriş Sağlanamadı", "receipt" => $receipt]);
        }
    }

    private function getClientUid(string $clienttoken)
    {
        $uid_redis = $this->checkRedisClientToken($clienttoken);
        if ($uid_redis) {
            return $uid_redis;
        }

        $uid_db = $this->checkDbClientToken($clienttoken);
        if ($uid_db) {
            return $uid_db->uid;
        }

        return false;
    }

    private function storeClientTokenInRedis(int $uid, string $clienttoken)
    {
        $uid_rediskey = "process_uid_{$uid}";
        Redis::set($uid_rediskey, $clienttoken, 'EX', 240);
    }

    protected function checkRedisSubStatus(string $clienttoken)
    {
        return Redis::hmget($clienttoken, "substatus");
    }

    protected function checkDbSubStatus(int $uid, int $appid)
    {
        return $this->subscription->findBy($uid, $appid);
    }

    protected function checkRedisClientToken(string $clienttoken)
    {
        return Redis::hexists($clienttoken, "uid");
    }

    protected function checkDbClientToken(string $clienttoken)
    {
        return $this->registerDevice->findBy("client-token", $clienttoken);
    }
}
