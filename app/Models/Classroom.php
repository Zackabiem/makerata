<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $primaryKey = 'class_id';

    protected $fillable = [
        'school_id',
        'name',
        'level',
        'arm',
        'form_master_id',
    ];

    public function school()
    {
        return $this->belongsTo(SchoolProfile::class, 'school_id');
    }

    public function formMaster()
    {
        return $this->belongsTo(Staff::class, 'form_master_id', 'staff_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'class_id', 'subject_id')
            ->withPivot('teacher_id')
            ->withTimestamps();
    }
}
