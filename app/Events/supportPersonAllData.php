<?php

namespace App\Events;

use Illuminate\Support\Facades\Log;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class SupportPersonAllData implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Public message property to broadcast
     *
     * @var bool
     */
    public $status;

    /**
     * Create a new event instance.
     *
     * @param bool $status
     * @return void
     */
    public function __construct(bool $status = false)
    {
        $this->status = $status;
        Log::info('SupportPersonAllData event fired with status: ' . $this->status);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('my-channel'); // important part of realtime notification
    }

    /**
     * Set the event name for broadcasting.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'my-event'; // important part of realtime notification
    }

    /**
     * Customize the data sent with the event.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $data = ['status' => $this->status];
        Log::info('Broadcasting data: ', $data);
        return $data;
    }
}
