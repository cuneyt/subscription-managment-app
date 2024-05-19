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

class SubscriptionController extends Controller
{
    protected $subscription;
    protected $device;
    protected $application;

    public function __construct(SubscriptionRepository $subscription, DeviceRepository $device, MobAppRepository $application)
    {
        $this->subscription = $subscription;
        $this->device = $device;
        $this->application = $application;
    }

    public function process(Request $data)
    {
        $validator = Validator::make($data->all(), [
            'appid' => 'required|integer',
            'uid' => 'required|integer', // deviceId
            'event' => 'required|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => false, "message" => "Doğru isteklerle ulaşılamadı!"], 200);
        }

        $appid = $data['appid'];
        $uid = $data['uid'];

        $clienttoken = Redis::get("process_uid_{$uid}");

        switch ($data['event']) {
            case "started":
                return $this->handleStartedEvent($appid, $uid, $clienttoken);
            case "renewed":
                return $this->handleRenewedEvent($appid, $uid, $clienttoken);
            case "canceled":
                return $this->handleCanceledEvent($appid, $uid, $clienttoken);
            default:
                return response()->json(["status" => false, "message" => "unknown event"], 400);
        }
    }

    private function handleStartedEvent($appid, $uid, $clienttoken)
    {
        $dateAfter60Min = date("Y-m-d H:i:s", strtotime("+60 minutes"));
        $data = [
            "appid" => $appid,
            "uid" => $uid,
            "expired_date" => $dateAfter60Min,
            "substatus" => true,
        ];
        $subCreate = $this->subscription->create($data);
        return $this->handleEventResponse($subCreate, $clienttoken, true);
    }

    private function handleRenewedEvent($appid, $uid, $clienttoken)
    {
        $subCreate = $this->subscription->update($uid, $appid); // renewed
        return $this->handleEventResponse($subCreate, $clienttoken, true);
    }

    private function handleCanceledEvent($appid, $uid, $clienttoken)
    {
        $subCanceled = $this->subscription->canceled($uid, $appid); // canceled
        return $this->handleEventResponse($subCanceled, $clienttoken, false);
    }

    private function handleEventResponse($subCreate, $clienttoken, $status)
    {
        if ($subCreate) {
            $udata = ['substatus' => $status];
            Redis::hmset($clienttoken, $udata);
            return response()->json(["status" => true, "message" => "event successful"], 200);
        } else {
            return response()->json(["status" => false, "message" => "event wrong"], 401);
        }
    }

    public function checksubs(Request $data)
    {
        $validator = Validator::make($data->all(), [
            'client-token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => false, "message" => "Doğru isteklerle ulaşılamadı!"], 200);
        }

        $clienttoken = $data['client-token'];
        $substatusRedis = $this->checkRedisSubStatus($clienttoken);
        $substatusCheck = $substatusRedis[0];

        if ($substatusCheck !== null) {
            $substatus = $substatusCheck == 1;
            return response()->json(["status" => true, "substatus" => $substatus, "message" => "Subscription status"], 200);
        }

        $substatusDb = $this->checkDbSubStatus($clienttoken);
        if ($substatusDb) {
            $substatus = $substatusDb == 1;
            return response()->json(["status" => true, "substatus" => $substatus, "message" => "Subscription status"], 200);
        }

        return response()->json(["status" => false, "message" => "Subscription not found"], 200);
    }

    protected function checkDbSubStatus(string $clienttoken)
    {
        $udata = $this->device->findBy("client-token", $clienttoken);
        $appid = $udata->AppId;
        $uid = $udata->uid;

        $subdata = $this->subscription->findBy($uid, $appid);
        if ($subdata) {
            return $subdata->substatus ? "1" : "2";
        }
        return false;
    }

    protected function checkRedisSubStatus(string $clienttoken)
    {
        return Redis::hmget($clienttoken, "substatus");
    }

    public function worker()
    {
        $resp = $this->subscription->getExpiredData();
        $result = [];

        foreach ($resp as $key => $value) {
            $appid = $value->appid;
            $uid = $value->uid;
            $appdata = $this->application->find($appid);
            $appclient = $appdata->uname;
            $appsecret = $appdata->pass;
            $receipt = "1"; // Tek sayı gönderiyorum çünkü tüm kayıtların pasif edilmesini istiyorum, tekrar Google verification yapılmasını istemiyorum.

            $url = 'http://localhost:8181/api/googleverification';
            $response = Http::withBasicAuth($appclient, $appsecret)->post($url, ['receipt' => $receipt, 'app' => $appid]);
            $json = json_decode($response->getBody()->getContents());

            if ($json->status) {
                event(new SubscriptionStatusChanged($appid, $uid, "renewed"));
                $result[$key] = ["uid" => $uid, "appid" => $appid, "message" => "başarılı"];
            } else {
                $result[$key] = ["uid" => $uid, "appid" => $appid, "message" => "başarısız"];
            }
        }

        if ($result) {
            return response()->json(["status" => true, "message" => $result]);
        }
        return response()->json(["status" => false, "message" => "aktif kayıt bulunmamaktadır."]);
    }
}
