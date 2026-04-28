<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QueueScannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $location = \App\Models\Location::create([
            'name' => 'Toko Pusat',
            'code' => 'TOKO01',
        ]);

        \App\Models\Location::create([
            'name' => 'Cabang Barat',
            'code' => 'TOKO02',
        ]);

        // Create a valid queue for today
        \App\Models\Queue::create([
            'location_id' => $location->id,
            'queue_number' => 'A-001',
            'nik' => '3212345678900001',
            'kk' => '3212345678900002',
            'status' => 'waiting',
            'qr_token' => '550e8400-e29b-41d4-a716-446655440000', // Example UUID
            'queue_date' => date('Y-m-d'),
        ]);

        \App\Models\Queue::create([
            'location_id' => $location->id,
            'queue_number' => 'A-002',
            'nik' => '3212345678909999',
            'kk' => '3212345678908888',
            'status' => 'waiting',
            'qr_token' => \Illuminate\Support\Str::uuid(),
            'queue_date' => date('Y-m-d'),
        ]);

        \App\Models\Staff::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'password' => bcrypt('password'),
            'role' => 'staff'
        ]);
    }
}
