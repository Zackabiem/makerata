<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use HasFactory;

    protected $primaryKey = 'fee_structure_id';

    protected $fillable = [
        'school_id',
        'class_id',
        'session_id',
        'term_id',
        'fee_category_id',
        'amount',
        'description',
    ];

    protected $casts = [
        'amount' => 'float',
    ];

    public function school()
    {
        return $this->belongsTo(SchoolProfile::class, 'school_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id', 'class_id');
    }

    public function category()
    {
        return $this->belongsTo(FeeCategory::class, 'fee_category_id', 'fee_category_id');
    }
}
