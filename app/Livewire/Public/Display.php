<?php

namespace App\Livewire\Public;

use Livewire\Component;
use App\Models\Queue;
use App\Models\Gate;
use Livewire\Attributes\Layout;

/**
 * Komponen Livewire untuk Layar Tampilan (Display).
 * Digunakan untuk menampilkan nomor antrean yang sedang dipanggil di setiap loket.
 */
class Display extends Component
{
    /**
     * Merender tampilan utama layar antrean.
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

        return view('livewire.public.display', [
            'gateDisplays' => $gateDisplays,
        ]);
    }
}
