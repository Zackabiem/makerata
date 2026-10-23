<?php

namespace App\Services;

use App\Models\TerminalScoreSheet;
use Illuminate\Support\Collection;

class RankingService
{
    /**
     * Calculates dense ordinal positions (1st, 2nd, 3rd...) for a given set of student total averages.
     * Ties share the same position rank.
     *
     * @param array $studentTotals Keyed by student_id => total_score
     * @return array Keyed by student_id => ordinal_position (e.g. "1st", "2nd")
     */
    public function calculatePositions(array $studentTotals): array
    {
        if (empty($studentTotals)) {
            return [];
        }

        // Sort descending by total score
        arsort($studentTotals);

        $rank = 1;
        $count = 0;
        $prevScore = null;
        $ranks = [];

        foreach ($studentTotals as $studentId => $score) {
            $count++;
            if ($prevScore !== null && $score < $prevScore) {
                $rank = $count;
            }
            $ranks[$studentId] = $this->formatOrdinal($rank);
            $prevScore = $score;
        }

        return $ranks;
    }

    /**
     * Convert integer rank into ordinal string (1st, 2nd, 3rd, 4th...)
     */
    public function formatOrdinal(int $number): string
    {
        if (in_array(($number % 100), [11, 12, 13])) {
            return $number . 'th';
        }

        switch ($number % 10) {
            case 1:  return $number . 'st';
            case 2:  return $number . 'nd';
            case 3:  return $number . 'rd';
            default: return $number . 'th';
        }
    }
}
