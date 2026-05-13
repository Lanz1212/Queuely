<?php

namespace App\Livewire\Public;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Service;
use App\Models\Queue;
use App\Models\QueueLog;
use Illuminate\Support\Str;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class Kiosk extends Component
{
    public $services;
    public $selectedService = null;
    public $driverName = '';
    public $phone = '';
    public $vehiclePlate = '';
    public $company = '';

    public $queueResult = null;
    public $qrCodeSvg = null;

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
        $this->qrCodeSvg = null;
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

        // Calculate day number from system start (first queue ever created)
        $firstQueue = Queue::orderBy('created_at', 'asc')->first();
        $systemStart = $firstQueue ? $firstQueue->created_at->startOfDay() : clone $today;
        $dayNumber = $systemStart->diffInDays($today) + 1; // +1 to make it 1-indexed

        // Get count for today for this service (NN)
        $countToday = Queue::where('service_id', $this->selectedService->id)
            ->where('created_at', '>=', $today)
            ->count();

        // Generate unique queue number with format M-DDNN
        $queueNumber = $this->selectedService->code_prefix . '-' .
                       str_pad($dayNumber, 2, '0', STR_PAD_LEFT) .
                       str_pad($countToday + 1, 2, '0', STR_PAD_LEFT);
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

        // Generate Server-side SVG QR Code
        $trackingUrl = route('tracking', $qrHash);
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'imageBase64' => false,
            'svgViewBoxSize' => 150,
            'addQuietzone' => false,
        ]);
        
        $this->qrCodeSvg = (new QRCode($options))->render($trackingUrl);
    }

    public function printReceipt()
    {
        // Simple client-side print trigger - do NOT reset here,
        // reset will be triggered from client after print dialog closes
        $this->dispatch('print-receipt');
    }

    #[On('reset-kiosk')]
    public function resetKiosk()
    {
        $this->cancel();
    }

    public function render()
    {
        return view('livewire.public.kiosk')->layout('components.layouts.app', ['title' => 'Kiosk Registrasi']);
    }
}
