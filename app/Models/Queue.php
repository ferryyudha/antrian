<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $fillable = [
        'location_id', 'queue_number', 'nik', 'kk', 'status', 'qr_token', 'queue_date'
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
