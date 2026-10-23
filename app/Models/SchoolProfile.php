<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolProfile extends Model
{
    use HasFactory;

    protected $table = 'school_profile';

    protected $fillable = [
        'owner_id',
        'school_name',
        'school_code',
        'plan',
        'plan_locked',
        'onboarding_completed',
        'subscription_expires_at',
        'motto',
        'school_address',
        'location',
        'email_address',
        'phone_number',
        'headteacher_name',
        'logo_url',
        'school_stamp_svg',
        'report_watermark',
        'report_template_config',
        'promotion_cutoff_average',
    ];

    protected $casts = [
        'plan_locked' => 'boolean',
        'onboarding_completed' => 'boolean',
        'subscription_expires_at' => 'datetime',
        'report_template_config' => 'array',
        'promotion_cutoff_average' => 'decimal:2',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'school_id');
    }

    public function staff()
    {
        return $this->hasMany(Staff::class, 'school_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'school_id');
    }
}
