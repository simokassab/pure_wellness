<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    protected $fillable = [
        'name',
        'uuid',
        'campaign_id',
        'description',
        'status',
    ];

    public function trackings()
    {
        return $this->hasMany(Tracking::class, 'project_id', 'id');
    }

//    uuid should be generated after project is created

    public function sources()
    {
        return $this->belongsToMany(Source::class, 'project_source')
            ->using(ProjectSource::class)
            ->withPivot('uuid', 'campaign_id')
            ->withTimestamps();
    }

}
