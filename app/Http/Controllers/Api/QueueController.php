<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\QueueService;
use App\Events\QueueVerified;
use Exception;

class QueueController extends Controller
{
    protected QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    public function takeQueue(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'nik' => 'required|string|size:16',
            'kk' => 'required|string|size:16',
            // 'captcha' => 'required' // TODO: Implement real captcha validation
        ]);

        try {
            $queue = $this->queueService->takeQueue($request->all());

            // Trigger realtime update for queue count
            event(new QueueVerified($queue));

            return response()->json([
                'status' => 'success',
                'data' => [
                    'queue_number' => $queue->queue_number,
                    'qr_token' => $queue->qr_token,
                ]
            ]);
        } catch (Exception $e) {
            // Ensure status code is integer and valid HTTP status
            $code = $e->getCode();
            if (!is_numeric($code) || $code < 100 || $code > 599) {
                $code = 500;
            }

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], (int)$code);
        }
    }

    public function getStatus($locationId)
    {
        $status = $this->queueService->getStatus($locationId);
        return response()->json([
            'status' => 'success',
            'data' => $status
        ]);
    }

    public function getLocations()
    {
        $today = date('Y-m-d');
        $locations = \App\Models\Location::all()->map(function($loc) use ($today) {
            $used = \App\Models\Queue::where('location_id', $loc->id)
                                     ->where('queue_date', $today)
                                     ->count();
            $loc->remaining_quota = max(0, $loc->quota - $used);
            return $loc;
        });

        return response()->json([
            'status' => 'success',
            'data' => $locations
        ]);
    }

    public function reprintQr(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $today = date('Y-m-d');
        $queue = \App\Models\Queue::where('queue_date', $today)
            ->where(function($q) use ($request) {
                $q->where('nik', $request->identifier)
                  ->orWhere('kk', $request->identifier)
                  ->orWhere('qr_token', $request->identifier);
            })
            ->whereIn('status', ['waiting', 'serving', 'done'])
            ->first();

        if (!$queue) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data antrian hari ini tidak ditemukan untuk NIK/KK tersebut.'
            ], 404);
        }

        // Ensure NIK and KK are explicitly visible
        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $queue->id,
                'queue_number' => $queue->queue_number,
                'nik' => (string)$queue->nik,
                'kk' => (string)$queue->kk,
                'status' => $queue->status,
                'qr_token' => $queue->qr_token,
                'location_id' => $queue->location_id
            ]
        ]);
    }
}
