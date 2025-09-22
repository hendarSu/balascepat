<?php

namespace App\Events;

use App\Models\Video;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoProgressUpdated implements ShouldBroadcast
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
        return 'video.progress';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->video->id,
            'progress' => (int) ($this->video->progress ?? 0),
            'status' => (string) $this->video->status,
        ];
    }
}

