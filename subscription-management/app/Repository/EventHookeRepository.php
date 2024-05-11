<?php


namespace App\Repository;
use App\Models\Eventhook;

class EventHookeRepository implements EventHookRepositoryInterface
{

    public function get()
    {
        return Eventhook::first();
    }

}
