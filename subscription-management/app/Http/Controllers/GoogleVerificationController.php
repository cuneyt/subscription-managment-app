<?php


namespace App\Http\Controllers;
use App\Repository\MobAppRepository;
use App\Repository\PaymentRepository;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use App\Events\SubscriptionStatusChanged;
use App\Repository\DeviceRepository;
use Illuminate\Support\Facades\Validator;

class GoogleVerificationController extends Controller
{
    protected $application;

    public function __construct(MobAppRepository $application)
    {
        $this->application = $application;
    }

    private function paymentHashCalc(string $hash): bool
    {
        $hashLastChar = substr($hash, -1);
        return $hashLastChar % 2 !== 0;
    }

    private function authenticate(Request $data, $appdata): bool
    {
        $authHeader = $data->header('Authorization');

        if ($authHeader && strpos($authHeader, 'Basic ') === 0) {
            $encodedCredentials = substr($authHeader, 6);
            $decodedCredentials = base64_decode($encodedCredentials);
            list($username, $password) = explode(':', $decodedCredentials, 2);

            return $username === $appdata->uname && $password === $appdata->pass;
        }

        return false;
    }

    public function verificationProcess(Request $data)
    {
        $validator = Validator::make($data->all(), [
            'receipt' => 'required|string',
            'app' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(["status" => false, "message" => "Doğru isteklerle ulaşılamadı!"], 200);
        }

        $appdata = $this->application->find($data->app);

        if (!$this->authenticate($data, $appdata)) {
            return response()->json(["status" => false, 'message' => 'Authentication failed'], 401);
        }

        $receiptStatus = $this->paymentHashCalc($data->receipt);
        $date = $this->getCurrentTime();

        return response()->json(["status" => $receiptStatus, "date" => $date]);
    }

    protected function getCurrentTime(): string
    {
        $timezoneName = timezone_name_from_abbr('', -6 * 3600, 0);
        $utcMinus6 = new DateTimeZone($timezoneName);
        $dateTime = new DateTime('now', $utcMinus6);
        return $dateTime->format('Y-m-d H:i:s');
    }
}

