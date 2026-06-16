<?php

namespace App\Livewire\Display;

use Livewire\Component;
use App\Models\Queue;
use App\Models\Gate;
use Livewire\Attributes\Layout;

/**
 * Komponen Livewire untuk Layar Tampilan (Display Board).
 * Digunakan untuk menampilkan nomor antrian yang sedang dipanggil di setiap loket.
 */
class Board extends Component
{
    /**
     * Merender tampilan utama layar antrian.
     * Mengambil seluruh loket aktif beserta antrian yang sedang diproses.
     */
    #[Layout('components.layouts.app')]
    public function render()
    {
        $gates = Gate::all();
        $gateDisplays = [];

        foreach ($gates as $gate) {
            $activeQueue = Queue::where('gate_id', $gate->id)
                ->whereIn('status', ['called', 'heading_to_gate', 'loading'])
                ->orderBy('called_at', 'desc')
                ->first();

            $gateDisplays[] = [
                'gate'  => $gate,
                'queue' => $activeQueue,
            ];
        }

        return view('livewire.display.board', [
            'gateDisplays' => $gateDisplays,
        ]);
    }
}
