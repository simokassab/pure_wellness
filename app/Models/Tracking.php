<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    protected $fillable = [
        'project_source_id',
        'source',
        'msisdn',
        'click_id',
        'anti_fraud_click_id',
        'mcp_uniq_id',
        'user_ip',
        'pixel_id',
        'campaign_id',
        'campaign_name',
        'ad_set_id',
        'ad_set_name',
        'ad_id',
        'ad_name',
        'utm_parameters',
        'additional_parameters',
        'success',
        'first_click',
        'second_click',
        'second_page_visit',
        'failure'
    ];

    //    relation with IntegrationLog
    public function integrationLogs()
    {
        return $this->hasMany(IntegrationLog::class, 'tracking_id', 'id');
    }

    public function projectSource()
    {
        return $this->belongsTo(ProjectSource::class, 'project_source_id', 'id');
    }


    protected $casts = [
        'utm_parameters' => 'array',
        'additional_parameters' => 'array',
    ];

    // Helper method to identify source from pixel ID
    public static function identifyClickId($request)
    {
        if ($request->has('gclid')) {
            return $request->input('gclid');
        } elseif ($request->has('ttclid')) {
            return $request->input('ttclid');
        } elseif ($request->has('fbclid')) {
            return $request->input('fbclid');
        }
        elseif ($request->has('click_id')) {
            return $request->input('click_id');
        }
        elseif ($request->has('clickId')) {
            return $request->input('clickId');
        }
    }

    // Helper method to get pixel ID based on source
    public static function getPixelId($request)
    {
        return $request->input('gclid') ??
            $request->input('ttclid') ??
            $request->input('fbclid') ??
            null;
    }

    // Helper method to collect UTM parameters
    public static function collectUtmParameters($request)
    {
        $utmParams = [];
        $utmFields = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];

        foreach ($utmFields as $field) {
            if ($request->has($field)) {
                $utmParams[$field] = $request->input($field);
            }
        }

        return $utmParams;
    }
}
