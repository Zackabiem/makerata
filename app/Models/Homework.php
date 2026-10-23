<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Homework extends Model
{
    use HasFactory;

    protected $table = 'homeworks';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'school_id',
        'title',
        'description',
        'class_id',
        'subject_id',
        'staff_id',
        'session_id',
        'term_id',
        'due_date',
        'file_url',
        'file_name',
        'max_score',
    ];

    protected $casts = [
        'due_date'  => 'datetime',
        'max_score' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id', 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id', 'subject_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }

    public function submissions()
    {
        return $this->hasMany(HomeworkSubmission::class, 'homework_id', 'id');
    }
}
