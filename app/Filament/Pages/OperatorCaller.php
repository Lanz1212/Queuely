<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Gate;
use App\Models\Queue;
use App\Models\QueueLog;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class OperatorCaller extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Loket';
    protected static ?int $navigationSort = 1;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';
    protected static ?string $navigationLabel = 'Loket Panggilan';
    protected static ?string $title = 'Loket Panggilan Antrian';

    protected string $view = 'filament.pages.operator-caller';

    public $selectedGateId = null;
    public $activeQueueId = null;

    public function mount()
    {
        // Default to first active gate
        $firstGate = Gate::where('status', 'ready')->first();
        if ($firstGate) {
            $this->selectedGateId = $firstGate->id;
        }
    }

    public function selectGate($gateId)
    {
        $this->selectedGateId = $gateId;
    }

    public function callNext()
    {
        if (!$this->selectedGateId) {
            Notification::make()->title('Pilih Gate terlebih dahulu')->danger()->send();
            return;
        }

        $gate = Gate::find($this->selectedGateId);
        if ($gate->status !== 'ready') {
            Notification::make()->title('Gate sedang tidak ready')->danger()->send();
            return;
        }

        // Find the oldest waiting queue
        $nextQueue = Queue::where('status', 'waiting')
            ->orderBy('created_at', 'asc')
            ->first();

        if (!$nextQueue) {
            Notification::make()->title('Tidak ada antrian menunggu')->warning()->send();
            return;
        }

        // Update current active queue in this gate to completed if it was just loading
        Queue::where('gate_id', $this->selectedGateId)
            ->whereIn('status', ['called', 'heading_to_gate', 'loading'])
            ->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

        // Call the next one
        $nextQueue->update([
            'status' => 'called',
            'gate_id' => $this->selectedGateId,
            'called_at' => now(),
        ]);

        QueueLog::create([
            'queue_id' => $nextQueue->id,
            'user_id' => auth()->id(),
            'action_type' => 'called',
            'old_status' => 'waiting',
            'new_status' => 'called',
            'notes' => 'Called to ' . $gate->name
        ]);

        // Trigger Audio in browser
        $this->dispatch('trigger-play-call', queueNumber: $nextQueue->queue_number, gateName: $gate->name);

        Notification::make()->title("Antrian {$nextQueue->queue_number} berhasil dipanggil")->success()->send();
    }
    
    public function callSpecific($queueId)
    {
        if (!$this->selectedGateId) {
            Notification::make()->title('Pilih Gate terlebih dahulu')->danger()->send();
            return;
        }

        $gate = Gate::find($this->selectedGateId);
        if ($gate->status !== 'ready') {
            Notification::make()->title('Gate sedang tidak ready')->danger()->send();
            return;
        }

        $queue = Queue::find($queueId);
        if (!$queue || $queue->status !== 'waiting') return;
        
        // Update current active queue in this gate to completed if it was just loading
        Queue::where('gate_id', $this->selectedGateId)
            ->whereIn('status', ['called', 'heading_to_gate', 'loading'])
            ->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);
            
        $queue->update([
            'status' => 'called',
            'gate_id' => $this->selectedGateId,
            'called_at' => now(),
        ]);
        
        QueueLog::create([
            'queue_id' => $queue->id,
            'user_id' => auth()->id(),
            'action_type' => 'called',
            'old_status' => 'waiting',
            'new_status' => 'called',
            'notes' => 'Specific call to ' . $gate->name
        ]);
        
        // Trigger Audio in browser
        $this->dispatch('trigger-play-call', queueNumber: $queue->queue_number, gateName: $gate->name);
        
        Notification::make()->title("Antrian {$queue->queue_number} berhasil dipanggil")->success()->send();
    }
    
    public function toggleGateStatus()
    {
        if (!$this->selectedGateId) return;
        $gate = Gate::find($this->selectedGateId);
        $gate->status = $gate->status === 'ready' ? 'busy' : 'ready'; // Simplification for Buka/Tutup
        $gate->save();
        
        Notification::make()->title('Status loket diperbarui')->success()->send();
    }

    public function changeStatus($queueId, $newStatus)
    {
        $queue = Queue::find($queueId);
        if (!$queue) return;

        $oldStatus = $queue->status;
        $queue->status = $newStatus;
        if ($newStatus == 'completed') {
            $queue->completed_at = now();
        }
        $queue->save();

        QueueLog::create([
            'queue_id' => $queue->id,
            'user_id' => auth()->id(),
            'action_type' => 'status_changed',
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        Notification::make()->title('Status antrian diperbarui')->success()->send();
    }
    
    public function recall($queueId)
    {
        $queue = Queue::find($queueId);
        if (!$queue) return;
        
        $queue->update(['called_at' => now()]);
        
        QueueLog::create([
            'queue_id' => $queue->id,
            'user_id' => auth()->id(),
            'action_type' => 'called',
            'old_status' => $queue->status,
            'new_status' => $queue->status,
            'notes' => 'Recalled'
        ]);
        
        Notification::make()->title("Antrian {$queue->queue_number} dipanggil ulang")->success()->send();
    }

    protected function getViewData(): array
    {
        $gates = Gate::all();
        $waitingQueues = Queue::where('status', 'waiting')->orderBy('created_at', 'asc')->get();
        $activeQueue = Queue::where('gate_id', $this->selectedGateId)
            ->whereIn('status', ['called', 'heading_to_gate', 'loading'])
            ->first();

        // Statistics
        $totalToday = Queue::whereDate('created_at', today())->count();
        $completedToday = Queue::whereDate('created_at', today())->where('status', 'completed')->count();

        return [
            'gates' => $gates,
            'waitingQueues' => $waitingQueues,
            'activeQueue' => $activeQueue,
            'totalToday' => $totalToday,
            'completedToday' => $completedToday,
        ];
    }
}
