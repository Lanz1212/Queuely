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

/**
 * Komponen Livewire untuk Mesin Kiosk/Anjungan Mandiri.
 * Menangani proses pengambilan tiket antrean oleh pelanggan atau pengemudi.
 */
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

    /**
     * Memuat daftar layanan yang aktif saat komponen pertama kali diinisialisasi.
     */
    public function mount()
    {
        $this->services = Service::where('is_active', true)->get();
    }

    /**
     * Memilih jenis layanan yang akan diambil antreannya.
     */
    public function selectService($serviceId)
    {
        $this->selectedService = Service::find($serviceId);
    }

    /**
     * Membatalkan proses dan mereset form ke kondisi awal.
     */
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

    /**
     * Mendaftarkan antrean baru ke dalam sistem.
     * Menggunakan transaction database untuk menghindari duplikasi nomor antrean pada saat bersamaan.
     */
    public function registerQueue()
    {
        $this->validate([
            'driverName'   => 'required|string|max:255',
            'phone'        => 'required|string|max:20',
            'vehiclePlate' => 'required|string|max:20',
            'company'      => 'required|string|max:255',
        ]);

        if (!$this->selectedService) {
            return;
        }

        // Membuat antrean secara aman menggunakan transaksi DB dan lockForUpdate
        // untuk mencegah nomor urut ganda pada hari yang sama.
        $queue = DB::transaction(function () {
            $today = now()->toDateString();

            $maxSequence = Queue::where('service_id', $this->selectedService->id)
                ->where('queue_date', $today)
                ->lockForUpdate()
                ->max('daily_sequence') ?? 0;

            $nextSequence = $maxSequence + 1;

            // Format nomor antrean, contoh: "M-001" (reset setiap hari baru)
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

        // Menghasilkan QR Code SVG dari sisi server yang mengarah ke halaman pelacakan
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

    /**
     * Memicu dialog cetak struk antrean pada browser pengguna.
     */
    public function printReceipt()
    {
        // Memicu event browser agar mencetak struk, reset terjadi setelah dialog ditutup
        $this->dispatch('print-receipt');
    }

    /**
     * Menangkap event 'reset-kiosk' dari browser untuk mereset tampilan Kiosk.
     */
    #[On('reset-kiosk')]
    public function resetKiosk()
    {
        $this->cancel();
    }

    /**
     * Merender tampilan utama dari Kiosk.
     */
    public function render()
    {
        return view('livewire.public.kiosk')->layout('components.layouts.app', ['title' => 'Cetak Antrian']);
    }
}
