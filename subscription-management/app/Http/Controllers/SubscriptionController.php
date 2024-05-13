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

    public function process(Request $data){

        $validator = Validator::make($data->all(), [
            'appid' => 'required|integer',
            'uid' => 'required|integer', // deviceId
            'event' => 'required|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json(["status"=>false,"message"=>"Doğru isteklerle ulaşılamadı!"],200);
        }

        $appid=$data['appid'];
        $uid=$data['uid'];

        $uid_keyredis = "process_uid_{$uid}";
        $clienttoken = Redis::get($uid_keyredis);

        switch ($data['event']) {
            case "started":
                $dateafter60min = date("Y-m-d H:i:s", strtotime("+60 minutes"));
                $data = [
                    "appid" => $appid,
                    "uid" => $uid,
                    "expired_date" => $dateafter60min,
                    "substatus" => true,
                ];
                $subcreate = $this->subscription->create($data);
                if ($subcreate) {
                    $udata = ['substatus' => $subcreate->substatus];
                    Redis::hmset($clienttoken, $udata);
                    return response()->json(["status" => true, "message" => "event successful"], 200);
                } else {
                    return response()->json(["status" => false, "message" => "event wrong"], 401);
                }
                break;

            case "renewed":
                $subcreate = $this->subscription->update($uid, $appid); //renewed
                if ($subcreate) {
                    $udata = ['substatus' => '1'];
                    Redis::hmset($clienttoken, $udata);
                    return response()->json(["status" => true, "message" => "event successful"], 200);
                } else {
                    return response()->json(["status" => false, "message" => "event wrong"], 401);
                }
                break;

            case "canceled":
                $subcanceled = $this->subscription->canceled($uid, $appid); //canceled
                if ($subcanceled) {
                    $udata = ['substatus' => 0];
                    Redis::hmset($clienttoken, $udata);
                    return response()->json(["status" => true, "message" => "event successful"], 200);
                } else {
                    return response()->json(["status" => false, "message" => "event wrong"], 401);
                }
                break;

            default:
                return response()->json(["status" => false, "message" => "unknown event"], 400);
        }

    }

    public function checksubs(Request $data){
        $validator = Validator::make($data->all(), [
            'client-token' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(["status"=>false,"message"=>"Doğru isteklerle ulaşılamadı!"],200);
        }
        $clienttoken = $data['client-token'];

        $substatus_redis = $this->checkRedisSubStatus($clienttoken);
        $substatus_check=$substatus_redis[0];
        $substatus_check=null;
        if($substatus_check!=null) {
            if ($substatus_check == 1){
                $substatus = true;
            }else{
                $substatus = false;
            }
            return response()->json(["status"=>true,"substatus"=>$substatus,"message"=>"Subscrition status"],200);
        }

        $substatus_db=$this->checkDbSubStatus($clienttoken);
        if($substatus_db){
            if ($substatus_db == 1){
                $substatus = true;
            }else{
                $substatus = false;
            }
            return response()->json(["status"=>true,"substatus"=>$substatus,"message"=>"Subscrition status"],200);
        }
        return response()->json(["status"=>false,"message"=>"Subscrition not found"],200);
    }

    protected function checkDbSubStatus(string $clienttoken){
        $udata = $this->device->findBy("client-token",$clienttoken);
        $appid=$udata->AppId;
        $uid=$udata->uid;

        $subdata = $this->subscription->findBy($uid,$appid);
        if($subdata){
            if($subdata->substatus){
                return "1";
            }else{
                return "2";
            }
        }
        return false;
    }

    protected function checkRedisSubStatus(string $clienttoken){
        return Redis::hmget($clienttoken,"substatus");
    }


    public function worker(){
        $resp = $this->subscription->getExpiredData();
        $result=array();
        foreach ($resp as $key => $value){

            $appid=$value->appid;
            $uid=$value->uid;
            $appdata = $this->application->find($appid);
            $appclient = $appdata->uname;
            $appsecret = $appdata->pass;
            $receipt = "1"; // tek sayı gönderiyorum çünkü tüm kayıtların pasif edilmesini istiyorum tekrar google verification yapılmasını istemiyorum.
            //$receipt = hash('sha256', $data['receipt'].rand()).rand(0,100);;

            $url = 'http://localhost:8181/api/googleverification';
            $response = Http::withBasicAuth($appclient, $appsecret)->post($url, ['receipt' => $receipt, 'app' => $appid]);
            $json = json_decode($response->getBody()->getContents());
            if($json->status){
                event(new SubscriptionStatusChanged($appid,$uid,"renewed"));
                $result[$key] = ["uid"=>"$uid", "appid" => $appid, "message"=>"başarılı"];
            }else{
                $result[$key] = ["uid"=>"$uid", "appid" => $appid, "message"=>"başarısız"];
            }
        }
        if($result){
            return response()->json(["status"=>true,"message"=>$result]);
        }
        return response()->json(["status"=>false,"message"=>"aktif kayıt bulunmamaktadır."]);
    }
}
