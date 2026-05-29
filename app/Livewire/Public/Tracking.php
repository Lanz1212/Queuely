<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Queue;
use Livewire\Attributes\Layout;

/**
 * Komponen Livewire untuk halaman Pelacakan Antrean (Tracking).
 * Memungkinkan pelanggan memantau status antrean mereka melalui QR Code.
 */
class Tracking extends Component
{
    public $qrHash;

    /**
     * Menerima parameter hash QR Code dari URL untuk mengidentifikasi antrean.
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
        // Mengambil data antrean berdasarkan hash QR code, akan gagal (404) jika tidak ditemukan
        $queue = Queue::where('qr_code_hash', $this->qrHash)->firstOrFail();
        
        $queuesAhead = 0;
        
        // Jika status masih menunggu, hitung jumlah antrean di depannya pada layanan yang sama
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
