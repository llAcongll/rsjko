<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RevenueService
{
    /**
     * Parse numeric string from CSV/Excel into float.
     * Handles various Indonesian/International formats.
     */
    public function parseNumeric($v)
    {
        if (empty($v))
            return 0;

        // Remove everything except numbers, hyphen, comma, and dot
        $v = preg_replace('/[^-0-9,.]/', '', $v);

        $lastComma = strrpos($v, ',');
        $lastDot = strrpos($v, '.');

        if ($lastComma !== false && $lastDot !== false) {
            // Mixed format: 1.234,56 or 1,234.56
            return ($lastComma > $lastDot)
                ? (float) str_replace(',', '.', str_replace('.', '', $v))
                : (float) str_replace(',', '', $v);
        }

        if ($lastComma !== false) {
            // Might be 1.234 or 1,23
            return (strlen($v) - $lastComma === 4)
                ? (float) str_replace(',', '', $v)
                : (float) str_replace(',', '.', $v);
        }

        if ($lastDot !== false) {
            // Might be 1,234 or 1.23
            return (strlen($v) - $lastDot === 4)
                ? (float) str_replace('.', '', $v)
                : (float) $v;
        }

        return (float) $v;
    }

    /**
     * Parse date from various common formats.
     */
    public function parseDate($dateStr)
    {
        $dateStr = trim($dateStr);
        if (empty($dateStr))
            return null;

        try {
            if (str_contains($dateStr, '/')) {
                return Carbon::createFromFormat('d/m/Y', $dateStr)->format('Y-m-d');
            }
            return Carbon::parse($dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            return $dateStr;
        }
    }

    /**
     * Wrap database operations in a transaction.
     */
    public function transaction(\Closure $callback)
    {
        return DB::transaction($callback);
    }
}
