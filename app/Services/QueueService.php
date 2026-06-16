<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\QueueLog;
use App\Models\Service;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Service untuk menangani seluruh proses pendaftaran antrian.
 * Memisahkan business logic dari Livewire component agar tetap bersih.
 */
class QueueService
{
    /**
     * Mendaftarkan antrian baru ke dalam sistem.
     * Menggunakan transaction database untuk menghindari duplikasi nomor antrian pada saat bersamaan.
     */
    public function register(Service $service, array $data): Queue
    {
        return DB::transaction(function () use ($service, $data) {
            $today = now()->toDateString();

            $maxSequence = Queue::where('service_id', $service->id)
                ->where('queue_date', $today)
                ->lockForUpdate()
                ->max('daily_sequence') ?? 0;

            $nextSequence = $maxSequence + 1;

            $queueNumber = $service->code_prefix . '-' .
                           str_pad($nextSequence, 3, '0', STR_PAD_LEFT);

            $queue = Queue::create([
                'queue_number'   => $queueNumber,
                'queue_date'     => $today,
                'daily_sequence' => $nextSequence,
                'service_id'     => $service->id,
                'status'         => 'waiting',
                'driver_name'    => $data['driverName'] ?: null,
                'phone'          => $data['phone'] ?: null,
                'vehicle_plate'  => $data['vehiclePlate'] ?: null,
                'company'        => $data['company'] ?: null,
                'qr_code_hash'   => Str::random(32),
                'registered_at'  => now(),
            ]);

            QueueLog::create([
                'queue_id'    => $queue->id,
                'action_type' => 'registered',
                'new_status'  => 'waiting',
            ]);

            return $queue;
        });
    }

    /**
     * Menghasilkan QR Code SVG untuk antrian yang diberikan.
     * SVG dirender secara langsung dari sisi server (offline-first).
     */
    public function generateQrCode(Queue $queue): string
    {
        $options = new QROptions([
            'outputType'     => QRCode::OUTPUT_MARKUP_SVG,
            'imageBase64'    => false,
            'svgViewBoxSize' => 150,
            'addQuietzone'   => false,
        ]);

        return (new QRCode($options))->render(
            route('tracking', $queue->qr_code_hash)
        );
    }
}
