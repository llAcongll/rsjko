<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SafeSpaceScreening extends Model
{
    protected $table = 'safe_space_screenings';

    protected $fillable = [
        'session_id',
        'school_id',
        'started_at',
        'completed_at',
        'score',
        'anxiety_result',
        'depression_result',
        'safety_answer',
        'safety_status',
        'follow_up',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'integer',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
