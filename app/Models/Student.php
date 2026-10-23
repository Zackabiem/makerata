<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $primaryKey = 'student_id';

    protected $fillable = [
        'school_id',
        'user_id',
        'class_id',
        'admission_no',
        'surname',
        'first_name',
        'other_name',
        'other_names',
        'gender',
        'date_of_birth',
        'parent_name',
        'parent_phone',
        'parent_email',
        'passport_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getFullNameAttribute()
    {
        $other = $this->other_name ?? $this->other_names ?? '';
        return trim("{$this->surname} {$this->first_name} {$other}");
    }

    public function school()
    {
        return $this->belongsTo(SchoolProfile::class, 'school_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id', 'class_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id', 'student_id');
    }

    public function scoreSheets()
    {
        return $this->hasMany(TerminalScoreSheet::class, 'student_id', 'student_id');
    }
}
