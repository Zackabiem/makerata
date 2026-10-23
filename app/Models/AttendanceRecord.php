<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $primaryKey = 'attendance_id';

    protected $fillable = [
        'school_id',
        'student_id',
        'class_id',
        'session_id',
        'term_id',
        'days_school_opened',
        'days_present',
        'days_absent',
        'remarks',
    ];

    protected $casts = [
        'days_school_opened' => 'integer',
        'days_present'       => 'integer',
        'days_absent'        => 'integer',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id', 'class_id');
    }
}
