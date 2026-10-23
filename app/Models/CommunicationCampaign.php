<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CommunicationCampaign extends Model
{
    use HasFactory;

    protected $table = 'communication_campaigns';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'school_id',
        'title',
        'type',
        'channel',
        'template_id',
        'subject',
        'body_html',
        'status',
        'scheduled_at',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
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

    public function template()
    {
        return $this->belongsTo(CommunicationTemplate::class, 'template_id', 'id');
    }
}
