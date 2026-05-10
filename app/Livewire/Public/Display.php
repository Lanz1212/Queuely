<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Queue;
use App\Models\Gate;
use App\Models\QueueLog;
use Livewire\Attributes\Layout;

class Display extends Component
{
    public $lastLogId = 0;

    public function mount()
    {
        $lastLog = QueueLog::where('action_type', 'called')->orderBy('id', 'desc')->first();
        if ($lastLog) {
            $this->lastLogId = $lastLog->id;
        }
    }

    #[Layout('components.layouts.app')]
    public function render()
    {
        // Get currently active gates and their processing/called queue
        $gates = Gate::all();
        $gateDisplays = [];

        foreach ($gates as $gate) {
            $activeQueue = Queue::where('gate_id', $gate->id)
                ->whereIn('status', ['called', 'heading_to_gate', 'loading'])
                ->orderBy('called_at', 'desc')
                ->first();

            $gateDisplays[] = [
                'gate' => $gate,
                'queue' => $activeQueue
            ];
        }

        // Check for new calls
        $newLogs = QueueLog::where('action_type', 'called')
            ->where('id', '>', $this->lastLogId)
            ->orderBy('id', 'asc')
            ->get();

        foreach ($newLogs as $log) {
            $queue = $log->queue;
            if ($queue && $queue->gate) {
                $this->dispatch(
                    'play-call',
                    queueNumber: $queue->queue_number,
                    gateName: $queue->gate->name
                );
            }
            $this->lastLogId = $log->id;
        }

        return view('livewire.public.display', [
            'gateDisplays' => $gateDisplays,
        ]);
    }
}
