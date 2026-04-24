<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\QueueService;
use Illuminate\Support\Facades\RateLimiter;
use Exception;

class QueueValidationController extends Controller
{
    protected QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    /**
     * Preview queue data using QR token.
     */
    public function scanPreview(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|uuid',
        ]);

        try {
            $data = $this->queueService->getPreviewData($request->qr_token);

            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Verify location code and process queue.
     */
    public function verifyQueue(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|uuid',
            'location_code' => 'required|string',
        ]);

        $qrToken = $request->qr_token;
        $rateLimitKey = 'verify-queue:' . $qrToken;

        // Limit attempts to 3 times per qr_token
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terlalu banyak percobaan (max 3x). Silakan coba lagi nanti.'
            ], 429);
        }

        try {
            $this->queueService->verifyQueue($qrToken, $request->location_code);
            
            // Clear rate limit on success
            RateLimiter::clear($rateLimitKey);

            return response()->json([
                'status' => 'success',
                'message' => 'Verifikasi berhasil, antrian diproses'
            ]);
        } catch (Exception $e) {
            // Increment attempt counter on failure
            if ($e->getMessage() === 'Kode toko tidak sesuai.') {
                RateLimiter::hit($rateLimitKey, 3600); // decay 1 hour
            }

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], $e->getCode() ?: 500);
        }
    }
}
