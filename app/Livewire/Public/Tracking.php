<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Queue;
use Livewire\Attributes\Layout;

class Tracking extends Component
{
    public $qrHash;

    public function mount($qr_code_hash)
    {
        $this->qrHash = $qr_code_hash;
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        $queue = Queue::where('qr_code_hash', $this->qrHash)->firstOrFail();
        
        $queuesAhead = 0;
        if (in_array($queue->status, ['waiting'])) {
            $queuesAhead = Queue::where('service_id', $queue->service_id)
                ->where('status', 'waiting')
                ->where('id', '<', $queue->id)
                ->count();
        }

        return view('livewire.public.tracking', [
            'queue' => $queue,
            'queuesAhead' => $queuesAhead,
            'estimatedMinutes' => $queuesAhead * ($queue->service->estimated_time ?? 15)
        ]);
    }
}
