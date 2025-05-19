<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Source extends Model
{
    protected $fillable = [
        'name',
        'uuid',
        'description',
        'status',
    ];

    public function trackings()
    {
        return $this->hasMany(Tracking::class, 'source_id', 'id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_source')
            ->using(ProjectSource::class)
            ->withPivot('uuid', 'campaign_id')
            ->withTimestamps();
    }

    public static function boot()
    {
        parent::boot();
        static::creating(function ($source) {
            $source->uuid = Str::uuid();
        });
    }
}
