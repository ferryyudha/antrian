<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Queue;
use App\Models\Location;
use App\Services\QueueService;

class AdminController extends Controller
{
    protected QueueService $queueService;

    public function __construct(QueueService $queueService)
    {
        $this->queueService = $queueService;
    }

    public function getQueues()
    {
        $queues = Queue::with('location')->whereDate('queue_date', date('Y-m-d'))->get();
        $locations = Location::all();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'queues' => $queues,
                'locations' => $locations
            ]
        ]);
    }

    public function cancelQueue(Request $request, $id)
    {
        try {
            $this->queueService->cancelQueue($id);
            return response()->json(['status' => 'success', 'message' => 'Antrian dibatalkan']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function updateQuota(Request $request, $id)
    {
        $request->validate(['quota' => 'required|integer|min:1']);
        $location = Location::find($id);
        if (!$location) return response()->json(['status' => 'error', 'message' => 'Lokasi tidak ditemukan'], 404);
        $location->update(['quota' => $request->quota]);
        return response()->json(['status' => 'success', 'message' => 'Kuota berhasil diupdate', 'data' => $location]);
    }

    public function storeLocation(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:locations,code',
            'quota' => 'required|integer|min:1'
        ]);

        $location = Location::create($request->only(['name', 'code', 'quota']));
        return response()->json(['status' => 'success', 'message' => 'Lokasi berhasil ditambahkan', 'data' => $location]);
    }

    public function updateLocation(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:locations,code,' . $id,
            'quota' => 'required|integer|min:1'
        ]);

        $location = Location::findOrFail($id);
        $location->update($request->only(['name', 'code', 'quota']));
        return response()->json(['status' => 'success', 'message' => 'Lokasi berhasil diubah', 'data' => $location]);
    }

    public function deleteLocation($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();
        return response()->json(['status' => 'success', 'message' => 'Lokasi berhasil dihapus']);
    }
}
