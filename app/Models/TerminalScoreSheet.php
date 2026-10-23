<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerminalScoreSheet extends Model
{
    use HasFactory;

    protected $table = 'terminal_score_sheets';

    protected $fillable = [
        'school_id',
        'student_id',
        'class_id',
        'subject_id',
        'session_id',
        'term_id',
        'first_test',
        'second_test',
        'third_test',
        'exam_score',
        'total_score',
        'grade',
        'remark',
    ];

    protected $appends = ['computed_total'];

    /**
     * Compute total score dynamically on-the-fly to handle nulls/blanks seamlessly.
     */
    public function getComputedTotalAttribute()
    {
        $t1 = is_numeric($this->first_test) ? (float)$this->first_test : 0;
        $t2 = is_numeric($this->second_test) ? (float)$this->second_test : 0;
        $t3 = is_numeric($this->third_test) ? (float)$this->third_test : 0;
        $ex = is_numeric($this->exam_score) ? (float)$this->exam_score : 0;

        return $t1 + $t2 + $t3 + $ex;
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
