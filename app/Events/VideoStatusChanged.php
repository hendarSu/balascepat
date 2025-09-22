<?php

namespace App\Events;

use App\Models\Video;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Video $video)
    {
    }

    public function broadcastOn(): Channel
    {
        return new Channel('videos');
    }

    public function broadcastAs(): string
    {
        return 'video.status';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->video->id,
            'status' => (string) $this->video->status,
        ];
    }
}

