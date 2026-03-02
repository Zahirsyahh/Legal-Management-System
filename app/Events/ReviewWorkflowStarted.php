<?php

namespace App\Events;

use App\Models\Contract;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReviewWorkflowStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $contract;
    public $stages;
    public $startedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(Contract $contract, $stages, $startedBy)
    {
        $this->contract = $contract;
        $this->stages = $stages;
        $this->startedBy = $startedBy;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('contract.' . $this->contract->id),
            new PrivateChannel('legal.department'),
            new PrivateChannel('admin.dashboard'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'review.workflow.started';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'contract_id' => $this->contract->id,
            'contract_title' => $this->contract->title,
            'contract_number' => $this->contract->contract_number,
            'started_by' => $this->startedBy->name,
            'started_at' => now()->toDateTimeString(),
            'total_stages' => count($this->stages),
            'first_stage' => $this->stages[0]['stage_name'] ?? null,
            'first_reviewer' => $this->stages[0]['assigned_user_name'] ?? null,
        ];
    }
}