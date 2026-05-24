<?php

namespace App\Livewire\Public;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Service;
use App\Models\Queue;
use App\Models\QueueLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
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
            'driverName'   => 'nullable|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'vehiclePlate' => 'nullable|string|max:20',
            'company'      => 'nullable|string|max:255',
        ]);

        if (!$this->selectedService) {
            return;
        }

        // Generate queue atomically inside a DB transaction.
        // lockForUpdate() prevents two concurrent kiosk requests from getting
        // the same daily_sequence number (race condition safe).
        $queue = DB::transaction(function () {
            $today = now()->toDateString();

            $maxSequence = Queue::where('service_id', $this->selectedService->id)
                ->where('queue_date', $today)
                ->lockForUpdate()
                ->max('daily_sequence') ?? 0;

            $nextSequence = $maxSequence + 1;

            // Display format: e.g. "M-001", "M-002" — resets to 001 each new day
            $queueNumber = $this->selectedService->code_prefix . '-' .
                           str_pad($nextSequence, 3, '0', STR_PAD_LEFT);

            $newQueue = Queue::create([
                'queue_number'    => $queueNumber,
                'queue_date'      => $today,
                'daily_sequence'  => $nextSequence,
                'service_id'      => $this->selectedService->id,
                'status'          => 'waiting',
                'driver_name'     => $this->driverName ?: null,
                'phone'           => $this->phone ?: null,
                'vehicle_plate'   => $this->vehiclePlate ?: null,
                'company'         => $this->company ?: null,
                'qr_code_hash'    => Str::random(32),
                'registered_at'   => now(),
            ]);

            QueueLog::create([
                'queue_id'    => $newQueue->id,
                'action_type' => 'registered',
                'new_status'  => 'waiting',
            ]);

            return $newQueue;
        });

        $this->queueResult = $queue;

        // Generate Server-side SVG QR Code (uses globally-unique qr_code_hash, NOT queue_number)
        $options = new QROptions([
            'outputType'      => QRCode::OUTPUT_MARKUP_SVG,
            'imageBase64'     => false,
            'svgViewBoxSize'  => 150,
            'addQuietzone'    => false,
        ]);

        $this->qrCodeSvg = (new QRCode($options))->render(
            route('tracking', $queue->qr_code_hash)
        );
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
