<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['staff_id', 'checkin_time', 'status', 'qr_token'];
}
