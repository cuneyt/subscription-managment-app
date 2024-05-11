<?php

namespace App\Http\Controllers;

use App\Repository\MobAppRepository;
use Illuminate\Http\Request;
use App\Repository\DeviceRepositoryInterface;
use function Illuminate\Foundation\Configuration\respond;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class RegisterDeviceController extends Controller
{
    protected $registerDevice;
    protected $application;

    public function __construct(DeviceRepositoryInterface $registerDevice, MobAppRepository $application)
    {
        $this->registerDevice = $registerDevice;
        $this->application = $application;
    }

    public function create(Request $data){

        $validator = Validator::make($data->all(), [
            'uid' => 'nullable|integer',
            'AppId' => 'required|integer',
            'os' => 'required|string|max:20',
            'language' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(["status"=>false,"message"=>"Doğru isteklerle ulaşılamadı!"],200);
        }

        $checkAppExist = $this->checkAppExist($data->AppId);
        if(!$checkAppExist){
            return response()->json(["status"=>false,"message"=>"App Problem"],200);
        }

        $checkRedisUid=$this->checkRedisUid($data->uid);
        if($data->uid && $checkRedisUid){
            return response()->json(["status"=>true,"client-token"=>$checkRedisUid],200);
        }


        $checkDbUid=$this->checkDbUid($data->uid);
        if($data->uid && $checkDbUid){
            return response()->json(["status"=>true,"client-token"=>$checkDbUid['client-token']],200);
        }

        $token = $this->handleDeviceRegistration($validator->valid());

        return response()->json(["status"=>true,"client-token"=>$token],200);
    }

    protected function handleDeviceRegistration(array $data){
        $client_token = md5(uniqid(mt_rand(),true));
        $data['client-token'] = $client_token;
        $device = $this->registerDevice->register($data);

        Redis::set("uid_{$device->uid}",$client_token);
        $udata = [
            'uid' => $device->uid,
        ];
        Redis::hmset($client_token, $udata);
        return $client_token;

    }

    protected function checkRedisUid(int $uid){
        $redisKey = "uid_{$uid}";
        return Redis::get($redisKey);
    }

    protected function checkDbUid(int $uid){
        return $this->registerDevice->find($uid);
    }

    protected function checkAppExist(int $appid){
        if (!is_numeric($appid)) {
            return false; // Geçersiz değer
        }

        $applicationstatus = $this->application->find($appid);
        if($applicationstatus){
            return $applicationstatus;
        }

        $application_data = [
        "appid" => $appid,
        "apptitle"=>"application_".rand(),
        "uname"=> "u_".rand(),
        "pass"=>"p_".rand(),
        ];
        $app = $this->application->create($application_data);
        return $app;
    }




}
