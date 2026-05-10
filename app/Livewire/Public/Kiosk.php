<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Service;
use App\Models\Queue;
use App\Models\QueueLog;
use Illuminate\Support\Str;

class Kiosk extends Component
{
    public $services;
    public $selectedService = null;
    public $driverName = '';
    public $phone = '';
    public $vehiclePlate = '';
    public $company = '';

    public $queueResult = null;

    public function mount()
    {
        $this->services = Service::where('is_active', true)->get();
    }

    public function selectService($serviceId)
    {
        $this->selectedService = Service::find($serviceId);
    }

    public function cancel()
    {
        $this->selectedService = null;
        $this->driverName = '';
        $this->phone = '';
        $this->vehiclePlate = '';
        $this->company = '';
        $this->queueResult = null;
    }

    public function registerQueue()
    {
        $this->validate([
            'driverName' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'vehiclePlate' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
        ]);

        if (!$this->selectedService)
            return;

        $today = now()->startOfDay();

        // Get count for today
        $countToday = Queue::where('service_id', $this->selectedService->id)
            ->where('created_at', '>=', $today)
            ->count();

        $queueNumber = $this->selectedService->code_prefix . str_pad($countToday + 1, 3, '0', STR_PAD_LEFT);
        $qrHash = Str::random(32);

        $queue = Queue::create([
            'queue_number' => $queueNumber,
            'service_id' => $this->selectedService->id,
            'status' => 'waiting',
            'driver_name' => $this->driverName,
            'phone' => $this->phone,
            'vehicle_plate' => $this->vehiclePlate,
            'company' => $this->company,
            'qr_code_hash' => $qrHash,
            'registered_at' => now(),
        ]);

        QueueLog::create([
            'queue_id' => $queue->id,
            'action_type' => 'registered',
            'new_status' => 'waiting',
        ]);

        $this->queueResult = $queue;
    }

    public function printReceipt()
    {
        // Simple client-side print trigger
        $this->dispatch('print-receipt');
        // Reset after printing
        $this->cancel();
    }

    public function render()
    {
        return view('livewire.public.kiosk')->layout('components.layouts.app', ['title' => 'Kiosk Registrasi']);
    }
}
