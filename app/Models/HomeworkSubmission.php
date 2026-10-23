<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HomeworkSubmission extends Model
{
    use HasFactory;

    protected $table = 'homework_submissions';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'homework_id',
        'student_id',
        'submission_text',
        'file_url',
        'file_name',
        'submitted_at',
        'score',
        'comment',
        'status',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'score'        => 'float',
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

    public function homework()
    {
        return $this->belongsTo(Homework::class, 'homework_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}
