<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingScale extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'grade',
        'min_score',
        'max_score',
        'remark',
    ];

    /**
     * Resolves the matching grade and remark for a given score.
     */
    public static function resolveScale(float $score, $schoolId)
    {
        $scale = self::where('school_id', $schoolId)
            ->where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->first();

        if ($scale) {
            return [
                'grade' => $scale->grade,
                'remark' => $scale->remark,
            ];
        }

        // Fallback standard grading logic
        if ($score >= 70) return ['grade' => 'A', 'remark' => 'EXCELLENT'];
        if ($score >= 60) return ['grade' => 'B', 'remark' => 'VERY GOOD'];
        if ($score >= 50) return ['grade' => 'C', 'remark' => 'GOOD'];
        if ($score >= 45) return ['grade' => 'D', 'remark' => 'PASS'];
        if ($score >= 40) return ['grade' => 'E', 'remark' => 'FAIR'];
        return ['grade' => 'F', 'remark' => 'FAIL'];
    }
}
