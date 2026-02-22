<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenandaTangan extends Model
{
    protected $fillable = [
        'jabatan',
        'pangkat',
        'nama',
        'nip',
    ];

    public function getNipAttribute($value)
    {
        if (!$value)
            return '';
        $nip = str_replace([' ', '.'], '', $value);
        if (strlen($nip) !== 18)
            return $value;

        return substr($nip, 0, 8) . ' ' .
            substr($nip, 8, 6) . ' ' .
            substr($nip, 14, 1) . ' ' .
            substr($nip, 15, 3);
    }

    public function setNipAttribute($value)
    {
        $this->attributes['nip'] = str_replace([' ', '.'], '', $value);
    }
}
