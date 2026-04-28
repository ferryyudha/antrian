<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\Location;
use App\Events\QueueVerified;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class QueueService
{
    /**
     * Get or create a queue for a user.
     */
    public function takeQueue(array $data): Queue
    {
        return DB::transaction(function () use ($data) {
            $today = date('Y-m-d');

            $existingQueue = Queue::where('queue_date', $today)
                                  ->where(function($q) use ($data) {
                                      $q->where('nik', $data['nik'])
                                        ->orWhere('kk', $data['kk']);
                                  })
                                  ->first();

            if ($existingQueue) {
                throw new Exception("NIK atau KK ini sudah terdaftar dalam antrian hari ini.", 400);
            }

            $location = Location::lockForUpdate()->findOrFail($data['location_id']);
            $currentCount = Queue::where('location_id', $location->id)
                                 ->where('queue_date', $today)
                                 ->count();

            if ($currentCount >= $location->quota) {
                throw new Exception("Kuota antrian untuk lokasi ini sudah penuh.", 400);
            }

            $nextNumber = $currentCount + 1;
            $queueNumber = 'A-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            return Queue::create([
                'location_id' => $location->id,
                'queue_number' => $queueNumber,
                'nik' => $data['nik'],
                'kk' => $data['kk'],
                'status' => 'waiting',
                'qr_token' => Str::uuid()->toString(),
                'queue_date' => $today,
            ]);
        });
    }

    /**
     * Get Realtime Status for a location.
     */
    public function getStatus(int $locationId): array
    {
        $today = date('Y-m-d');

        $currentServing = Queue::where('location_id', $locationId)
                               ->where('queue_date', $today)
                               ->whereIn('status', ['serving', 'done'])
                               ->orderBy('updated_at', 'desc')
                               ->first();

        $lastDone = Queue::where('location_id', $locationId)
                         ->where('queue_date', $today)
                         ->where('status', 'done')
                         ->orderBy('updated_at', 'desc')
                         ->limit(10)
                         ->pluck('queue_number')
                         ->toArray();

        $totalWaiting = Queue::where('location_id', $locationId)
                             ->where('queue_date', $today)
                             ->where('status', 'waiting')
                             ->count();

        return [
            'current' => $currentServing ? $currentServing->queue_number : '-',
            'last_done' => $lastDone,
            'total_waiting' => $totalWaiting,
        ];
    }

    /**
     * Admin action to cancel a queue.
     */
    public function cancelQueue(int $id): bool
    {
        $queue = Queue::findOrFail($id);
        if ($queue->status === 'done') {
            throw new Exception("Antrian yang sudah selesai tidak bisa dibatalkan.", 400);
        }
        
        $queue->update(['status' => 'cancelled']);
        event(new QueueVerified($queue));
        return true;
    }

    /**
     * Get preview data from a QR token.
     */
    public function getPreviewData(string $qrToken): array
    {
        $queue = Queue::where('qr_token', $qrToken)->first();

        if (!$queue) {
            throw new Exception("QR tidak ditemukan.", 404);
        }

        if ($queue->queue_date !== date('Y-m-d')) {
            throw new Exception("QR expired atau tidak valid untuk hari ini.", 400);
        }

        if (!in_array($queue->status, ['waiting', 'serving', 'done'])) {
            throw new Exception("Antrian sudah dibatalkan.", 400);
        }

        return [
            'queue_number' => $queue->queue_number,
            'nik' => $this->maskString($queue->nik),
            'kk' => $this->maskString($queue->kk),
            'location_id' => $queue->location_id,
            'is_verified' => $queue->status === 'done'
        ];
    }

    /**
     * Verify the queue using the location code and update status to done.
     */
    public function verifyQueue(string $qrToken, string $locationCode): bool
    {
        return DB::transaction(function () use ($qrToken, $locationCode) {
            $queue = Queue::where('qr_token', $qrToken)
                          ->with('location')
                          ->lockForUpdate() // Prevent race conditions
                          ->first();

            if (!$queue) {
                throw new Exception("QR tidak ditemukan.", 404);
            }

            if ($queue->queue_date !== date('Y-m-d')) {
                throw new Exception("QR expired.", 400);
            }

            if (!in_array($queue->status, ['waiting', 'serving'])) {
                throw new Exception("QR sudah digunakan.", 400);
            }

            if (!$queue->location || $queue->location->code !== $locationCode) {
                throw new Exception("Kode toko tidak sesuai.", 400);
            }

            $queue->update([
                'status' => 'done'
            ]);

            // Optional Realtime Broadcasting
            event(new QueueVerified($queue));

            return true;
        });
    }

    /**
     * Masks a string, leaving only the last 4 characters visible.
     */
    private function maskString(string $string): string
    {
        $length = strlen($string);
        if ($length <= 4) return $string;
        return str_repeat('*', $length - 4) . substr($string, -4);
    }
}
