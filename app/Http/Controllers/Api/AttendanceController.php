<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function checkIn(Request $request)
    {
        $request->validate([
            'qr_token' => 'required|string',
        ]);

        // Simple mock validation: in a real app, the staff QR token 
        // would be a signed hash or UUID unique to the staff
        // Here we just look up the staff by ID in the token for simplicity (e.g. "STAFF-1")
        $tokenParts = explode('-', $request->qr_token);
        if (count($tokenParts) != 2 || $tokenParts[0] !== 'STAFF') {
            return response()->json(['status' => 'error', 'message' => 'QR Staff tidak valid'], 400);
        }

        $staff = Staff::find($tokenParts[1]);
        if (!$staff) {
            return response()->json(['status' => 'error', 'message' => 'Staff tidak ditemukan'], 404);
        }

        Attendance::create([
            'staff_id' => $staff->id,
            'checkin_time' => now(),
            'status' => 'present',
            'qr_token' => $request->qr_token
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Absensi berhasil untuk ' . $staff->name
        ]);
    }
}
