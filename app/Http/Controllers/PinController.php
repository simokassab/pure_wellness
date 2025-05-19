<?php

namespace App\Http\Controllers;

use App\Models\IntegrationLog;
use App\Models\ProjectSource;
use App\Models\Tracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class PinController extends Controller
{
    private array $config = [
        'shortcode' => 4600,
        'channelId' => 22718
    ];

    public function pin(Request $request)
    {
        if (!$request->has('source')) {
            return redirect('failure?errors=source_not_found');
        }
        $source = ProjectSource::where('uuid', $request->input('source'))->first();
        if (!$source) {
            return redirect('failure?errors=source_not_found');
        }
        $trackingData = [
            'project_source_id' => $source->id,
            'msisdn' => null,
            'source' => 'PIN',
            'click_id' => Tracking::identifyClickId($request),
            'first_click' => false,
            'second_click' => false,
            'user_ip' => $request->ip(),
            'pixel_id' => Tracking::getPixelId($request),
            'campaign_id' => $request->input('campaign_id'),
            'campaign_name' => $request->input('campaign_name'),
            'ad_set_id' => $request->input('ad_set_id'),
            'ad_set_name' => $request->input('ad_set_name'),
            'ad_id' => $request->input('ad_id'),
            'ad_name' => $request->input('ad_name'),
            'utm_parameters' => Tracking::collectUtmParameters($request),
            'additional_parameters' => $request->except([
                'msisdn', 'campaign_id', 'campaign_name', 'source', 'pixel_id',
                'ad_set_id', 'ad_set_name', 'ad_id', 'ad_name',
                'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'
            ])
        ];

        session(['tracking_data' => $trackingData]);

        Tracking::updateOrCreate(
            [
                'click_id' => $trackingData['click_id'],
                'project_source_id' => $source->id
            ],
            $trackingData
        );
        return view('pin', ['trackingData' => $trackingData]);
    }

    public function storeTracking(Request $request)
    {
//        try {
            // Validate the request
            $request->validate([
                'click_id' => 'required|string',
//                'user_headers' => 'required'
            ]);

            // Get the tracking data from session
            $trackingData = session('tracking_data', []);

            // Update with the click-specific data
            $clickId = $request->input('click_id');

            // Create the tracking record
            $tracking = Tracking::where('click_id', $clickId)->where('source', 'PIN')
                ->where('project_source_id', $trackingData['project_source_id'])
                ->first();
            $tracking->msisdn = $request->input('msisdn');
            $tracking->first_click = true;
            $tracking->save();
            return response()->json([
                'success' => true,
                'tracking_id' => $tracking->id
            ]);

//        } catch (\Exception $e) {
//            Log::error('Error storing tracking data: ' . $e->getMessage());
//            return response()->json([
//                'success' => false,
//                'message' => 'Failed to store tracking data'
//            ], 500);
//        }
    }


    public function getAntiFraudScript(Request $request)
    {
//        try {
            $baseUrl = 'http://iq.asiacell.gph.digitalads.digitalabs.ae:9090/Shield/AntiFraud/Prepare/';
            $queryParams = [
                'Page' => $request->page,
                'ChannelID' => intval($this->config['channelId']),
                'ClickID' => $request->click_id,
                'Headers' => $request->user_headers,
                'UserIP' => $request->user_ip,
            ];

            if ($request->page == '2'){
                $queryParams['MSISDN'] = $request->msisdn;

            }
            else {
                $queryParams['MSISDN'] = '';
            }

//            dd($queryParams, $baseUrl . '?' . http_build_query($queryParams));
            $project_source = ProjectSource::where('uuid', $request->source)->first();
            $tracking = Tracking::where('click_id', $request->click_id)->where('source', 'PIN')
                ->where('project_source_id',$project_source->id)
                ->first();

            $integration = IntegrationLog::updateOrCreate(
                [
                    'provider' => 'anti_fraud',
                    'tracking_id' => $tracking->id,
                    'event_type' => 'request',
                    'status' => 'success',
                ],
                [
                    'payload' => $queryParams,
                    'url' => $baseUrl,
                ]);

            // Make the request
            $response = Http::get($baseUrl . '?' . http_build_query($queryParams));
//            i want to check if response header has AntiFrauduniqid
            if ($response->header('AntiFrauduniqid')) {
                $integration->status = 'success';
                $integration->metadata = [
                    'header' => $response->headers(),
                ];
            }
            else {
                $integration->status = 'failed';
                $integration->error_message = $response->body();
                $integration->metadata = [
                    'body' => $response->body()
                ];
            }
            $integration->save();
            $tracking->anti_fraud_click_id = $response->header('AntiFrauduniqid');
            $tracking->msisdn = $request->msisdn;
            $tracking->first_click = true;
            $tracking->save();
            if (!$response->successful()) {
                redirect('failure');
                throw new \Exception('Anti-fraud API request failed');
            }

            if ($request->save_antifraud == '1') {
                $tracking->anti_fraud_click_id = $response->header('AntiFrauduniqid');
            }
            if ($request->page == '1'){
                $tracking->mcp_uniq_id = $response->header('Mcpuniqid');
            }

            $tracking->save();

            // Get the script from response body and AntiFrauduniqid from header
            return Response::json([
                'success' => true,
                'script' => $response->body(),
                'antiFrauduniqid' => $response->header('AntiFrauduniqid'),
                'mcp_uniq_id' => $response->header('Mcpuniqid'),
            ]);

//        } catch (\Exception $e) {
//            return Response::json([
//                'success' => false,
//                'message' => $e->getMessage()
//            ], 500);
//        }
    }

    public function getPinCode(Request $request)
    {

        $url = 'http://iq.asiacell.gph.digitalads.digitalabs.ae:9090/PIN/actions/sendPincode';
        $tracking = Tracking::where('click_id', $request->click_id)
            ->where('source', 'PIN')
            ->first();

        $queryParams = [
            'ChannelID' => intval($this->config['channelId']),
            'ClickID' => $tracking->click_id,
            'msisdn' => $request->msisdn,
            'shortcode' => intval($this->config['shortcode']),
            'LanguageID' => $request->languageId,
            'campaignId' => 131 // Changed to match documentation case
        ];
        // Make the request
        $response = Http::get($url . '?' . http_build_query($queryParams));
        $bodyContent = $response->json(); // Parse JSON response
        Log::error('response: ' . json_encode($bodyContent));
        // Log the interaction
        $integration = IntegrationLog::updateOrCreate(
            [
                'provider' => 'digitalads',
                'tracking_id' => $tracking->id,
                'event_type' => 'request',
                'status' => 'success',
            ],
            [
                'payload' => $queryParams,
                'url' => $url,
            ]
        );
        if ($response->status() == 200) {
            $integration->status = 'success';
            $integration->metadata = [
                'header' => $response->headers(),
                'body' => $bodyContent
            ];
            $integration->save();

            // Check response code for redirection
            if (isset($bodyContent['Success']) &&
                isset($bodyContent['Code']) && $bodyContent['Code'] == "10200") {
                $integration = IntegrationLog::updateOrCreate(
                    [
                        'provider' => 'digitalads',
                        'tracking_id' => $tracking->id,
                        'event_type' => 'response',
                        'status' => 'success',
                    ],
                    [
                        'payload' => $bodyContent,
                        'url' => $url,
                    ]
                );
                // Success case - redirect to OTP route with params
                return Response::json([
                    'success' => true
                ]);
            } else {
                $integration = IntegrationLog::updateOrCreate(
                    [
                        'provider' => 'digitalads',
                        'tracking_id' => $tracking->id,
                        'event_type' => 'response',
                        'status' => 'failed',
                    ],
                    [
                        'payload' => $bodyContent,
                        'url' => $url,
                    ]
                );
                return Response::json([
                    'success' => false,
                    'code' => $bodyContent['Code'] ?? null,
                    'message' => $bodyContent['Message'] ?? 'Unknown error',

                ]);
            }
        } else {
            // HTTP error case
            $integration->status = 'failed';
            $integration->error_message = $response->body();
            $integration->metadata = [
                'body' => $response->body()
            ];
            $integration->save();

            return Response::json([
                'success' => false,
                'code' => 'error',
                'message' => 'Failed to send PIN code'
            ]);
        }
    }

    public function handleSubscription(Request $request): \Illuminate\Http\JsonResponse
    {
        $clickId = $request->input('clickId');
        $msisdn = $request->input('msisdn');
        $tracking = Tracking::where('click_id', $clickId)->where('msisdn', $msisdn)
            ->where('source', 'PIN')
            ->first();
        if ($tracking){
            $tracking->second_click = true;
            $tracking->save();
        }
        else {
            Log::error('Tracking not found for clickId: ' . $clickId . ' and msisdn: ' . $msisdn);
        }
        $url = 'http://iq.asiacell.gph.digitalads.digitalabs.ae:9090/PIN/actions/verifyPincode';
        $queryParams = [
            'ChannelID' => intval($this->config['channelId']),
            'ClickID' => $tracking->anti_fraud_click_id,
            'msisdn' => $tracking->msisdn,
            'shortcode' => intval($this->config['shortcode']),
            'pin' => $request->input('otpCode'),
            'AntiFrauduniqid' => $tracking->anti_fraud_click_id,
            'campaignId' => 131,
            'LanguageID' => intval($request->input('languageId')),
            'advertiser' => 'digitalads-google',
        ];
        Log::error('HANDLE SUBSCRIPTION');
        Log::error('queryParams: ' . json_encode($queryParams));
        Log::error($url . '?' . http_build_query($queryParams));
        // Make the request
        $response = Http::get($url . '?' . http_build_query($queryParams));
        $integration = IntegrationLog::updateOrCreate(
            [
                'provider' => 'digitalads-pin',
                'tracking_id' => $tracking->id,
                'event_type' => 'request',
                'status' => 'success',
            ],
            [
                'payload' => $queryParams,
                'url' => $url,
            ]
        );

        $bodyContent = $response->json();
        Log::error('response: ' . json_encode($bodyContent));
        if ($response->status() == 200) {


            // Check response code for redirection
            if (isset($bodyContent['Success']) &&
                isset($bodyContent['Code']) && $bodyContent['Code'] == "10300") {
                $integration = IntegrationLog::updateOrCreate(
                    [
                        'provider' => 'digitalads-pin',
                        'tracking_id' => $tracking->id,
                        'event_type' => 'response',
                        'status' => 'success',
                    ],
                    [
                        'payload' => $bodyContent,
                        'url' => $url,
                    ]
                );
                // Success case - redirect to OTP route with params
                return Response::json([
                    'success' => true,
                    'msisdn' => $tracking->msisdn,
                    'click_id' => $tracking->anti_fraud_click_id,
                ]);
            } else {
                $integration = IntegrationLog::updateOrCreate(
                    [
                        'provider' => 'digitalads',
                        'tracking_id' => $tracking->id,
                        'event_type' => 'response',
                        'status' => 'failed',
                    ],
                    [
                        'payload' => $bodyContent,
                        'url' => $url,
                    ]
                );
                $tracking->failure = 1;
                $tracking->save();
                return Response::json([
                    'success' => false,
                    'code' => $bodyContent['Code'] ?? null,
                    'msisdn' => $tracking->msisdn,
                    'message' => $bodyContent['Message'] ?? 'Unknown error',
                    'af_id' => $tracking->anti_fraud_click_id,
                ]);
            }
        } else {
            // HTTP error case
            $integration->status = 'failed';
            $integration->error_message = $response->body();
            $integration->metadata = [
                'body' => $response->body()
            ];
            $integration->save();
            $tracking->failure = 1;
            $tracking->save();
            return Response::json([
                'success' => false,
                'code' => 'error',
                'msisdn' => $tracking->msisdn,
                'message' => 'Failed to send PIN code'
            ]);
        }
    }

    public function otpVerification(Request $request)
    {
        if (!$request->has('source')) {
            return redirect('failure?errors=source_not_found');
        }
        $source = ProjectSource::where('uuid', $request->input('source'))->first();
        if (!$source) {
            return redirect('failure?errors=source_not_found');
        }
        $tracking = Tracking::where('click_id', $request->click_id)
            ->where('msisdn', $request->msisdn)
            ->where('project_source_id', $source->id)
            ->first();
        if ($tracking) {
            $tracking->first_click = true;
            $tracking->second_page_visit = true;
            $tracking->save();
        }
        return view('otp');
    }
}
