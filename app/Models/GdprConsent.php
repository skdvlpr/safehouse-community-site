<?php

namespace App\Models;

use Database\Factories\GdprConsentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GdprConsent extends Model
{
    /** @use HasFactory<GdprConsentFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'consent_type',
        'granted',
        'ip_hash',
        'consented_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'granted' => 'boolean',
            'consented_at' => 'datetime',
        ];
    }
}
