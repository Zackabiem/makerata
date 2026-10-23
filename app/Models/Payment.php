<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'school_id',
        'student_id',
        'session_id',
        'term_id',
        'reference_no',
        'receipt_no',
        'amount_paid',
        'payment_method',
        'payment_date',
        'channel_details',
        'recorded_by_staff_id',
        'status',
        'remarks',
    ];

    protected $casts = [
        'amount_paid'  => 'float',
        'payment_date' => 'date',
    ];

    public function school()
    {
        return $this->belongsTo(SchoolProfile::class, 'school_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function recorder()
    {
        return $this->belongsTo(Staff::class, 'recorded_by_staff_id', 'staff_id');
    }
}
