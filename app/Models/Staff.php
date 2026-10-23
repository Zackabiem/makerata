<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';
    protected $primaryKey = 'staff_id';

    protected $fillable = [
        'school_id',
        'user_id',
        'surname',
        'first_name',
        'email',
        'phone',
        'address',
        'role',
        'status',
    ];

    public function getFullNameAttribute()
    {
        return trim("{$this->surname} {$this->first_name}");
    }

    public function school()
    {
        return $this->belongsTo(SchoolProfile::class, 'school_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
