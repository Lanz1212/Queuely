<?php

namespace App\Livewire\Kiosk;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Service;
use App\Services\QueueService;

/**
 * Komponen Livewire untuk Mesin Kiosk/Anjungan Mandiri.
 * Menangani antarmuka pengambilan tiket antrian oleh pelanggan atau pengemudi.
 * Business logic pendaftaran didelegasikan ke QueueService.
 */
class Register extends Component
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
     * Mendaftarkan antrian baru ke dalam sistem via QueueService.
     */
    public function registerQueue(QueueService $queueService)
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

        $queue = $queueService->register($this->selectedService, [
            'driverName'   => $this->driverName,
            'phone'        => $this->phone,
            'vehiclePlate' => $this->vehiclePlate,
            'company'      => $this->company,
        ]);

        $this->queueResult = $queue;
        $this->qrCodeSvg   = $queueService->generateQrCode($queue);
    }

    /**
     * Memicu dialog cetak struk antrian pada browser pengguna.
     */
    public function printReceipt()
    {
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
        return view('livewire.kiosk.register')
            ->layout('components.layouts.app', ['title' => 'Cetak Antrian']);
    }
}
