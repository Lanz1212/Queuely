<?php

namespace App\Services;

use App\Models\Gate;
use App\Models\Queue;
use App\Models\QueueLog;

/**
 * Service untuk menangani seluruh operasi pemanggilan antrian oleh operator.
 * Memisahkan business logic dari Filament Page agar tetap bersih.
 */
class OperatorQueueService
{
    /**
     * Memanggil antrian berikutnya yang sedang menunggu ke loket yang dipilih.
     * Antrian aktif sebelumnya di loket tersebut otomatis ditandai selesai.
     */
    public function callNext(int $gateId, int $userId): ?Queue
    {
        $gate = Gate::find($gateId);

        Queue::where('gate_id', $gateId)
            ->whereIn('status', ['called', 'heading_to_gate', 'loading'])
            ->update(['status' => 'completed', 'completed_at' => now()]);

        $nextQueue = Queue::where('status', 'waiting')
            ->orderBy('created_at', 'asc')
            ->first();

        if (!$nextQueue) {
            return null;
        }

        $nextQueue->update([
            'status'    => 'called',
            'gate_id'   => $gateId,
            'called_at' => now(),
        ]);

        QueueLog::create([
            'queue_id'    => $nextQueue->id,
            'user_id'     => $userId,
            'action_type' => 'called',
            'old_status'  => 'waiting',
            'new_status'  => 'called',
            'notes'       => 'Called to ' . $gate->name,
        ]);

        return $nextQueue;
    }

    /**
     * Memanggil antrian tertentu berdasarkan ID ke loket yang dipilih.
     */
    public function callSpecific(int $queueId, int $gateId, int $userId): ?Queue
    {
        $gate = Gate::find($gateId);
        $queue = Queue::find($queueId);

        if (!$queue || $queue->status !== 'waiting') {
            return null;
        }

        Queue::where('gate_id', $gateId)
            ->whereIn('status', ['called', 'heading_to_gate', 'loading'])
            ->update(['status' => 'completed', 'completed_at' => now()]);

        $queue->update([
            'status'    => 'called',
            'gate_id'   => $gateId,
            'called_at' => now(),
        ]);

        QueueLog::create([
            'queue_id'    => $queue->id,
            'user_id'     => $userId,
            'action_type' => 'called',
            'old_status'  => 'waiting',
            'new_status'  => 'called',
            'notes'       => 'Specific call to ' . $gate->name,
        ]);

        return $queue;
    }

    /**
     * Mengubah status antrian dan memperbarui status loket terkait jika diperlukan.
     */
    public function changeStatus(int $queueId, string $newStatus, int $userId): void
    {
        $queue = Queue::find($queueId);
        if (!$queue) {
            return;
        }

        $oldStatus   = $queue->status;
        $queue->status = $newStatus;

        if ($newStatus === 'completed') {
            $queue->completed_at = now();
        }
        $queue->save();

        if ($queue->gate_id) {
            $gate = Gate::find($queue->gate_id);
            if ($gate) {
                if ($newStatus === 'loading') {
                    $gate->status = 'busy';
                    $gate->save();
                } elseif ($newStatus === 'completed') {
                    $gate->status = 'ready';
                    $gate->save();
                }
            }
        }

        QueueLog::create([
            'queue_id'    => $queue->id,
            'user_id'     => $userId,
            'action_type' => 'status_changed',
            'old_status'  => $oldStatus,
            'new_status'  => $newStatus,
        ]);
    }

    /**
     * Memanggil ulang antrian yang sudah dipanggil sebelumnya.
     */
    public function recall(int $queueId, int $userId): ?Queue
    {
        $queue = Queue::find($queueId);
        if (!$queue) {
            return null;
        }

        $queue->update(['called_at' => now()]);

        QueueLog::create([
            'queue_id'    => $queue->id,
            'user_id'     => $userId,
            'action_type' => 'called',
            'old_status'  => $queue->status,
            'new_status'  => $queue->status,
            'notes'       => 'Recalled',
        ]);

        return $queue;
    }

    /**
     * Mengubah status loket antara 'ready' dan 'busy'.
     */
    public function toggleGateStatus(int $gateId): Gate
    {
        $gate = Gate::find($gateId);
        $gate->status = $gate->status === 'ready' ? 'busy' : 'ready';
        $gate->save();

        return $gate;
    }
}
