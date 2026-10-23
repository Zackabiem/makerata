<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeCategory extends Model
{
    use HasFactory;

    protected $primaryKey = 'fee_category_id';

    protected $fillable = [
        'school_id',
        'name',
        'description',
        'is_compulsory',
    ];

    protected $casts = [
        'is_compulsory' => 'boolean',
    ];

    public function school()
    {
        return $this->belongsTo(SchoolProfile::class, 'school_id');
    }

    public function feeStructures()
    {
        return $this->hasMany(FeeStructure::class, 'fee_category_id', 'fee_category_id');
    }
}
