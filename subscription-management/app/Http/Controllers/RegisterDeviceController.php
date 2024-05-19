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

    public function create(Request $data)
    {
        $validator = Validator::make($data->all(), [
            'uid' => 'required|integer',
            'AppId' => 'required|integer',
            'os' => 'required|string|max:20',
            'language' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => false, "message" => "Doğru isteklerle ulaşılamadı!"], 200);
        }

        if (!$this->checkAppExist($data->AppId)) {
            return response()->json(["status" => false, "message" => "App Problem"], 200);
        }

        if ($clientToken = $this->getClientToken($data->uid)) {
            return response()->json(["status" => true, "client-token" => $clientToken], 200);
        }

        $token = $this->handleDeviceRegistration($validator->validated());

        return response()->json(["status" => true, "client-token" => $token], 200);
    }

    protected function handleDeviceRegistration(array $data): string
    {
        $clientToken = md5(uniqid(mt_rand(), true));
        $data['client-token'] = $clientToken;
        $device = $this->registerDevice->register($data);

        Redis::set("uid_{$device->uid}", $clientToken);
        Redis::hmset($clientToken, ['uid' => $device->uid]);

        return $clientToken;
    }

    protected function getClientToken(int $uid): ?string
    {
        return $this->checkRedisUid($uid) ?: $this->checkDbUid($uid)['client-token'] ?? null;
    }

    protected function checkRedisUid(int $uid): ?string
    {
        return Redis::get("uid_{$uid}");
    }

    protected function checkDbUid(int $uid): ?array
    {
        return $this->registerDevice->find($uid);
    }

    protected function checkAppExist(int $appid): bool
    {
        if (!is_numeric($appid)) {
            return false;
        }

        $applicationStatus = $this->application->find($appid);
        if ($applicationStatus) {
            return true;
        }

        $applicationData = [
            "appid" => $appid,
            "apptitle" => "application_" . rand(),
            "uname" => "u_" . rand(),
            "pass" => "p_" . rand(),
        ];

        $this->application->create($applicationData);
        return true;
    }
}
