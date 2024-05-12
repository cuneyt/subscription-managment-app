<?php

namespace App\Listeners;

use App\Events\SubscriptionStatusChanged;
use App\Models\Eventhook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use App\Repository\EventHookeRepository;
use Illuminate\Support\Facades\Redis;

class ProcessListener
{

    protected $eventHookeRepository;
    /**
     * Create the event listener.
     */
    public function __construct(EventHookeRepository $eventHookeRepository)
    {
        $this->eventHookeRepository=$eventHookeRepository;
    }

    /**
     * Handle the event.
     */
    public function handle(SubscriptionStatusChanged $event): void
    {
        //$hook = $this->eventHookeRepository->get();

        $endpointurl = "http://localhost:8181/api/eventChange";

        $data = [
            'appid' => $event->appId,
            'uid' => $event->deviceId,
            'event' => $event->eventType
        ];

        $response = Http::post($endpointurl,$data);

        if($response->successful()){
            //echo "process basarili";
        }else{
            //echo "process basarisiz";
        }
    }
}
