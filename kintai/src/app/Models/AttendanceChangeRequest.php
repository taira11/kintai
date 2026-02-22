<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'requested_by',
        'approved_by',
        'status',
        'target_field',
        'before_value',
        'after_value',
        'reason',
        'approved_at',
    ];

    protected $casts = [
        'before_value' => 'datetime',
        'after_value'  => 'datetime',
        'approved_at'  => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
