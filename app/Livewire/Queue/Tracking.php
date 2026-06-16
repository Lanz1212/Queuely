<?php

namespace App\Livewire\Queue;

use Livewire\Component;
use App\Models\Queue;
use Livewire\Attributes\Layout;

/**
 * Komponen Livewire untuk halaman Pelacakan Antrian (Tracking).
 * Memungkinkan pelanggan memantau status antrian mereka melalui QR Code.
 */
class Tracking extends Component
{
    public $qrHash;

    /**
     * Menerima parameter hash QR Code dari URL untuk mengidentifikasi antrian.
     */
    public function mount($qr_code_hash)
    {
        $this->qrHash = $qr_code_hash;
    }

    /**
     * Merender halaman pelacakan beserta estimasi waktu tunggu.
     */
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

        return view('livewire.queue.tracking', [
            'queue'            => $queue,
            'queuesAhead'      => $queuesAhead,
            'estimatedMinutes' => $queuesAhead * ($queue->service->estimated_time ?? 15),
        ]);
    }
}
