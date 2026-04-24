<?php

namespace App\Listeners;

use App\Events\QueueVerified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

use Illuminate\Support\Facades\Http;

class NotifyNodeServer
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(QueueVerified $event): void
    {
        try {
            Http::post('http://localhost:3000/broadcast', [
                'location_id' => $event->queueItem->location_id
            ]);
        } catch (\Exception $e) {
            // Log error silently, don't break the application
            \Log::error('NodeJS Server Unreachable: ' . $e->getMessage());
        }
    }
}
