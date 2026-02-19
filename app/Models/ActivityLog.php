<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'target_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper Static method to log activity
     */
    public static function log($action, $module, $description = null, $targetId = null, $oldValues = null, $newValues = null)
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => $module,
            'target_id' => $targetId,
            'description' => $description,
            'old_values' => self::formatData($oldValues),
            'new_values' => self::formatData($newValues),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Clean up data for human-readable logging
     */
    protected static function formatData($data)
    {
        if (!$data || !is_array($data))
            return $data;

        // Fields to omit from log
        $hidden = ['id', 'created_at', 'updated_at', 'deleted_at', 'user_id', 'password', 'remember_token'];

        $clean = array_diff_key($data, array_flip($hidden));

        foreach ($clean as $key => $value) {
            // Unset if value is null to keep it clean
            if (is_null($value)) {
                unset($clean[$key]);
                continue;
            }

            // Format ISO Dates (Eloquent toArray() format)
            if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $value)) {
                try {
                    $dt = \Carbon\Carbon::parse($value);
                    $clean[$key] = $dt->toDateTimeString();

                    // If it's a date only (time is 00:00:00), just show the date
                    if ($dt->hour === 0 && $dt->minute === 0 && $dt->second === 0) {
                        $clean[$key] = $dt->toDateString();
                    }
                } catch (\Exception $e) {
                }
            }
        }

        return $clean;
    }
}
