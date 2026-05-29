<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Queue;
use App\Models\Gate;
use App\Models\QueueLog;
use Livewire\Attributes\Layout;

/**
 * Komponen Livewire untuk Layar Tampilan (Display).
 * Digunakan untuk menampilkan nomor antrean yang sedang dipanggil di setiap loket.
 */
class Display extends Component
{
    public $lastLogId = 0;

    /**
     * Menginisialisasi data awal, mencatat ID log terakhir untuk mendeteksi panggilan baru.
     */
    public function mount()
    {
        $lastLog = QueueLog::where('action_type', 'called')->orderBy('id', 'desc')->first();
        if ($lastLog) {
            $this->lastLogId = $lastLog->id;
        }
    }

    /**
     * Merender tampilan utama layar antrean dan memeriksa apakah ada panggilan baru.
     */
    #[Layout('components.layouts.app')]
    public function render()
    {
        // Mengambil seluruh loket yang aktif berserta antrean yang sedang diproses
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

        // Memeriksa log panggilan baru untuk memicu notifikasi suara/tampilan
        $newLogs = QueueLog::where('action_type', 'called')
            ->where('id', '>', $this->lastLogId)
            ->orderBy('id', 'asc')
            ->get();

        foreach ($newLogs as $log) {
            $queue = $log->queue;
            if ($queue && $queue->gate) {
                // Memicu event browser untuk memutar suara panggilan
                $this->dispatch(
                    'play-call',
                    queueNumber: $queue->queue_number,
                    gateName: $queue->gate->name
                );
            }
            $this->lastLogId = $log->id; // Memperbarui status log terakhir
        }

        return view('livewire.public.display', [
            'gateDisplays' => $gateDisplays,
        ]);
    }
}
