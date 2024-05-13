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
        $this->application= $application;
    }

    private function paymenthashcalc(String $hash)
    {
        $hashlastchar= substr($hash,-1);
        $hashcalc = $hashlastchar % 2;

        if($hashcalc == 0){
            return false;
        }else{
            return true;
        }
    }

    public function verificationprocess(Request $data){
        $validator = Validator::make($data->all(), [
            'receipt' => 'required|string',
            'app' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(["status"=>false,"message"=>"Doğru isteklerle ulaşılamadı!"],200);
        }

        $authHeader = $data->header('Authorization');

        $appdata = $this->application->find($data->app);
        $uname = $appdata->uname;
        $upass = $appdata->pass;

        if ($authHeader && strpos($authHeader, 'Basic ') === 0) {
            $encodedCredentials = substr($authHeader, 6);
            $decodedCredentials = base64_decode($encodedCredentials);

            // Kullanıcı adı ve parolayı `username:password` formatında alalım
            list($username, $password) = explode(':', $decodedCredentials, 2);


            if ($username != $uname || $password != $upass) {

                return response()->json(["status"=>false,'message' => 'Authenticated failed',],401);
            }
        }else{
            echo "hata";
        }

        $receipt=$data->receipt;

        $receiptStatus = $this->paymenthashcalc($receipt);
        $date = $this->timezone();
        if($receiptStatus){
            return response()->json(["status"=>true,"date"=>$date]);
        }else{
            return response()->json(["status"=>false,"date"=>$date]);
        }
    }

    protected function checkRedisClientToken(int $uid){
        $redisKey = "uid_{$uid}";
        return Redis::get($redisKey);
    }

    protected function timezone(){
        $timezoneName = timezone_name_from_abbr('', -6 * 3600, 0);
        $utcMinus6 = new DateTimeZone($timezoneName);
        $dateTime = new DateTime('now', $utcMinus6);
        $formattedDate = $dateTime->format('Y-m-d H:i:s');
        return $formattedDate;
    }

}
