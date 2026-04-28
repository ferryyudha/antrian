<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueVerified implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $queueItem;

    /**
     * Create a new event instance.
     */
    public function __construct(\App\Models\Queue $queue)
    {
        $this->queueItem = $queue;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('queue-updates'),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'queue_number' => $this->queueItem->queue_number,
            'status' => $this->queueItem->status,
            'location_id' => $this->queueItem->location_id,
        ];
    }
}
