<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Gate;
use App\Models\Queue;
use App\Services\OperatorQueueService;
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

        $nextQueue = app(OperatorQueueService::class)->callNext($this->selectedGateId, auth()->id());

        if (!$nextQueue) {
            Notification::make()->title('Tidak ada antrian menunggu')->warning()->send();
            return;
        }

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

        $queue = app(OperatorQueueService::class)->callSpecific($queueId, $this->selectedGateId, auth()->id());

        if (!$queue) {
            return;
        }

        Notification::make()->title("Antrian {$queue->queue_number} berhasil dipanggil")->success()->send();
    }
    
    public function toggleGateStatus()
    {
        if (!$this->selectedGateId) {
            return;
        }

        app(OperatorQueueService::class)->toggleGateStatus($this->selectedGateId);

        Notification::make()->title('Status loket diperbarui')->success()->send();
    }

    public function changeStatus($queueId, $newStatus)
    {
        app(OperatorQueueService::class)->changeStatus($queueId, $newStatus, auth()->id());

        Notification::make()->title('Status antrian diperbarui')->success()->send();

        // Auto-call next queue when completed
        if ($newStatus === 'completed' && $this->selectedGateId) {
            $this->callNext();
        }
    }

    public function recall($queueId)
    {
        $queue = app(OperatorQueueService::class)->recall($queueId, auth()->id());

        if ($queue) {
            Notification::make()->title("Antrian {$queue->queue_number} dipanggil ulang")->success()->send();
        }
    }

    protected function getViewData(): array
    {
        $gates = Gate::all();
        $waitingQueues = Queue::where('status', 'waiting')->orderBy('created_at', 'asc')->get();
        $activeQueue = Queue::where('gate_id', $this->selectedGateId)
            ->whereIn('status', ['called', 'heading_to_gate', 'loading'])
            ->first();

        // Statistics
        $totalToday     = Queue::today()->count();
        $completedToday = Queue::today()->where('status', 'completed')->count();

        return [
            'gates' => $gates,
            'waitingQueues' => $waitingQueues,
            'activeQueue' => $activeQueue,
            'totalToday' => $totalToday,
            'completedToday' => $completedToday,
        ];
    }
}
