<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BehaviorAssessment extends Model
{
    use HasFactory;

    protected $primaryKey = 'assessment_id';

    protected $fillable = [
        'school_id',
        'student_id',
        'class_id',
        'session_id',
        'term_id',
        // Affective Domain (1-5)
        'punctuality',
        'neatness',
        'politeness',
        'honesty',
        'cooperation',
        'leadership',
        'emotional_stability',
        'attitude_to_work',
        // Psychomotor Domain (1-5)
        'handwriting',
        'verbal_fluency',
        'sports',
        'crafts',
        'drawing',
        'musical_skills',
    ];

    protected $casts = [
        'punctuality'         => 'integer',
        'neatness'            => 'integer',
        'politeness'          => 'integer',
        'honesty'             => 'integer',
        'cooperation'         => 'integer',
        'leadership'          => 'integer',
        'emotional_stability' => 'integer',
        'attitude_to_work'    => 'integer',
        'handwriting'         => 'integer',
        'verbal_fluency'      => 'integer',
        'sports'              => 'integer',
        'crafts'              => 'integer',
        'drawing'             => 'integer',
        'musical_skills'      => 'integer',
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
