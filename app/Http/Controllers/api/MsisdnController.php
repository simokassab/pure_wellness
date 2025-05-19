<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Tracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MsisdnController extends Controller
{
    public function sendMsisdn(Request $request)
    {
        $msisdn = $request->input('msisdn');
        if (!$msisdn) {
            return response()->json([
                'message' => 'MSISDN is required',
            ], 400);
        }
        else {
            $tracking = Tracking::where('msisdn', $msisdn)
                ->where('success', 0)->where('first_click', 1)
                ->where('second_click', 1)
                ->where('second_page_visit', 1)
                ->first();
            if ($tracking) {
                // Make a post call to the uploader API
                try {
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer R0NMSURfMjAyNV9VUExPQURFUl9UT0tFTg==',
                        'Content-Type' => 'application/json',
                    ])->post('http://92.204.249.3:9999/api/uploader', [
                        'gclid' => $tracking->click_id,
                        'customer_id' => '6522424529',
                        'conversion_action_id' => '7093912200',
                        'conversion_currency_code' => 'USD',
                        'conversion_date_time' => $tracking->created_at->format('Y-m-d H:i:s'),
                    ]);

                    if ($response->successful()) {
                        // Update tracking record if needed
                        $tracking->update(['success' => 1]);
                        $tracking->update(['failure' => 0]);
                        return $response->json();
                    } else {
                        // Log the error or handle it as needed
                        Log::error('Failed to upload data', [
                            'response' => $response->body(),
                            'status' => $response->status(),
                            'msisdn' => $msisdn,
                        ]);
                        return response()->json([
                            'message' => 'Failed to upload data',
                            'error' => $response->body(),
                            'status' => $response->status()
                        ], $response->status());
                    }
                } catch (\Exception $e) {
                    // Catch any exceptions that might occur
                    Log::error('Error while making request', [
                        'error' => $e->getMessage(),
                        'msisdn' => $msisdn,
                    ]);
                    return response()->json([
                        'message' => 'Error while making request',
                        'status' => 500,
                        'error' => $e->getMessage()
                    ], 500);
                }
            }
            // If no tracking record is found
            Log::error('No eligible tracking record found', [
                'msisdn' => $msisdn,
            ]);
            return response()->json([
                'message' => 'No eligible tracking record found',
                'status' => 404
            ], 404);
        }
    }
}
