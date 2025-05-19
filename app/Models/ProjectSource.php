<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;

class ProjectSource extends Pivot
{
    protected $table = 'project_source';

    protected $fillable = [
        'project_id',
        'source_id',
        'campaign_id',
        'uuid',
    ];

    public $incrementing = true; // Necessary for auto-incrementing IDs in pivot table

    public function trackings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Tracking::class);
    }

    public function project(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function source(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    // Automatically generate a UUID on creation
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $str = Str::random(4);
            while ($this->check_uuid($str)) {
                $str = Str::random(4);
            }
            $model->uuid = $str;
        });
    }

    protected function check_uuid($uuid)
    {
        return $this->where('uuid', $uuid)->exists();
    }
}
