<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerminalRemark extends Model
{
    use HasFactory;

    protected $primaryKey = 'remark_id';

    protected $fillable = [
        'school_id',
        'student_id',
        'class_id',
        'session_id',
        'term_id',
        'form_master_remark',
        'principal_remark',
        'promotion_status',
        'next_term_begins',
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
