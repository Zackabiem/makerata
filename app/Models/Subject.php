<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $primaryKey = 'subject_id';

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'category',
        'description',
    ];

    public function school()
    {
        return $this->belongsTo(SchoolProfile::class, 'school_id');
    }

    public function classes()
    {
        return $this->belongsToMany(Classroom::class, 'class_subject', 'subject_id', 'class_id')
            ->withPivot('teacher_id')
            ->withTimestamps();
    }
}
