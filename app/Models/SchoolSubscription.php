<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SchoolSubscription extends Model
{
    use HasFactory;

    protected $table = 'school_subscriptions';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'school_id',
        'plan_id',
        'status',
        'student_count',
        'base_amount',
        'student_amount',
        'total_amount',
        'billing_period',
        'academic_year',
        'term',
        'reference',
        'grace_days',
        'start_date',
        'end_date',
        'next_billing_date',
        'auto_renew',
        'cancelled_at',
    ];

    protected $casts = [
        'base_amount'       => 'float',
        'student_amount'    => 'float',
        'total_amount'      => 'float',
        'start_date'        => 'date',
        'end_date'          => 'date',
        'next_billing_date' => 'date',
        'auto_renew'        => 'boolean',
        'cancelled_at'      => 'datetime',
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

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id', 'id');
    }

    public function school()
    {
        return $this->belongsTo(SchoolProfile::class, 'school_id', 'id');
    }
}
