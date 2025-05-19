<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    protected $fillable = [
        'provider',
        'tracking_id',
        'event_type',
        'payload',
        'url',
        'status',
        'error_message',
        'metadata'
    ];
    protected $casts = [
        'metadata' => 'array',
        'payload' => 'array'
    ];

    public function tracking()
    {
        return $this->belongsTo(Tracking::class, 'tracking_id', 'id');
    }

    public static function log($provider, $eventType, $payload = null, $status = 'success', $errorMessage = null, $metadata = [])
    {
        return self::create([
            'provider' => $provider,
            'event_type' => $eventType,
            'payload' => $payload,
            'status' => $status,
            'error_message' => $errorMessage,
            'metadata' => $metadata
        ]);
    }
}
