<?php

namespace App\Http\Controllers;

use App\Models\IntegrationLog;
use App\Models\ProjectSource;
use App\Models\Redirect;
use App\Models\Tracking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Http;

class HeController extends Controller
{
    private array $config = [
        'serviceId' => '911',
        'spId' => '251',
        'shortcode' => '3368',
        'servicename' => 'Pure Wellness'
    ];
    // Common headers where mobile carriers might send MSISDN
    private $msisdnHeaders = [
        'X-MSISDN',
        'MSISDN',
        'X-UP-CALLING-LINE-ID',
        'X-HTS-CLID',
        'MSISDN_NUMBER',
        'X-ORIGINAL-MSISDN',
        'X-TATA-MSISDN'
    ];

    private $digitalAdsBaseUrl = 'http://callback.digitalabs.ae:9090/actions/';

    public function index(Request $request)
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
            'source' => 'HE',
            'msisdn' => null,
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
                'msisdn' => null,
                'click_id' => $trackingData['click_id'],
                'project_source_id' => $source->id
            ],
            $trackingData
        );

        return view('index');
    }

    public function storeTracking(Request $request)
    {
//        try {
            // Validate the request


            // Get the tracking data from session
            $trackingData = session('tracking_data', []);

            // Update with the click-specific data
            $clickId = $request->input('click_id');
            $trackingData['msisdn'] = $request->input('msisdn');

            // Create the tracking record
            $tracking = Tracking::where('click_id', $clickId)
                ->where('project_source_id', $trackingData['project_source_id'])
                ->first();
            $tracking->first_click = true;
            $tracking->anti_fraud_click_id = $request->input('script_id');
            $tracking->save();

//             redirect to http://ziq-he.prime-build.co:8090/HE/oneclick/subscribeUser.php?serviceId=$serviceId&spId=$spId&shortcode=$shortcode&ti=$ti&ts=$ts&servicename=$servicename&merchantname=$merchantname

            $baseUrl = 'http://ziq-he.prime-build.co:8090/HE/oneclick/subscribeUser.php';
            $queryParams = [
                'serviceId' => $this->config['serviceId'],
                'spId' => $this->config['spId'],
                'shortcode' => $this->config['shortcode'],
                'ti' => Session::get('transaction_id'), // Transaction ID from session
                'ts' => time(), // Current timestamp
                'servicename' => $this->config['servicename'],
                'merchantname' => 'Prime Build',
                'ClickID' => $tracking->anti_fraud_click_id,
            ];


//    return the redirect URL
            $redirectUrl = $baseUrl . '?' . http_build_query($queryParams);
            Log::info('Redirect URL generated: ' . $redirectUrl . ' for click_id: ' . $clickId);
            IntegrationLog::updateOrCreate(
                [
                    'provider' => 'SDP',
                    'tracking_id' => $tracking->id,
                    'event_type' => 'request',
                    'status' => 'success',
                ],
                [
                    'payload' => $queryParams,
                    'url' => $redirectUrl,
                ]
            );
//            dd($redirectUrl);
            return response()->json([
                'success' => true,
                'tracking_id' => $tracking->id,
                'redirect_url' => $redirectUrl,
            ]);

//        } catch (\Exception $e) {
//            Log::error('Error storing tracking data: ' . $e->getMessage());
//            return response()->json([
//                'success' => false,
//                'message' => 'Failed to store tracking data'
//            ], 500);
//        }
    }

    public function verify(Request $request)
    {
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
        return view('verify');
    }

    public function success(Request $request)
    {
        $msisdn = $request->msisdn;
        $anti_fraud_click_id = $request->ClickID;
        $tracking = Tracking::where('anti_fraud_click_id', $anti_fraud_click_id)->first();
        if ($tracking) {
            $tracking->success = true;
            $tracking->failure = false;
            $tracking->msisdn = $msisdn ?? null;
            $tracking->second_click = true;
            $tracking->second_page_visit = true;
            $tracking->save();
            IntegrationLog::updateOrCreate(
                [
                    'provider' => 'SDP',
                    'tracking_id' => $tracking->id,
                    'event_type' => 'response',
                    'status' => 'success',
                ],
                [
                    'payload' => [
                        'body' => $request->all()
                    ],
                    'url' => '',
                    'error_message' => null,

                ]);

            $query_params = [
                'clickId' => $anti_fraud_click_id,
                'campaignId' => $tracking->projectSource->campaign_id,
                'userIP' => $tracking->user_ip,
            ];
            $integration = IntegrationLog::firstOrCreate([
                'provider' => 'digital_ads',
                'tracking_id' => $tracking->id,
                'event_type' => 'request',
                'status' => 'success',
                'url' => $this->digitalAdsBaseUrl . 'SuccessCallBack',
            ], [
                'payload' => $query_params,
            ]);
            if ($integration->wasRecentlyCreated) {
                $response_digital_ads = Http::get($this->digitalAdsBaseUrl . 'SuccessCallBack?' . http_build_query($query_params));

                if ($response_digital_ads->successful()) {
                    $integration->status = 'success';
                    $integration->metadata = [
                        'body' => $response_digital_ads->body()
                    ];
                }
                else {
                    $integration->status = 'failed';
                    $integration->error_message = $response_digital_ads->body();
                    $integration->metadata = [
                        'body' => $response_digital_ads->body()
                    ];
                }
            }

        }
        return view('success');
    }

    public function failure(Request $request)
    {
        $msisdn = $request->msisdn;
        $anti_fraud_click_id = $request->ClickID;
        $tracking = Tracking::where('msisdn', $msisdn)->first();
//        log all the query params in the url
        Log::error('Failure URL Query Params:', $request->query());
        if ($tracking) {
            $tracking->failure = true;
            $tracking->save();
            IntegrationLog::updateOrCreate(
                [
                    'provider' => 'SDP',
                    'tracking_id' => $tracking->id,
                    'event_type' => 'response',
                    'status' => 'failed',
                ],
                [
                    'payload' => [
                        'body' => $request->all()
                    ],
                    'url' => '',
                    'error_message' => json_encode($request->all()),
                ]);

            $query_params = [
                'clickId' => $anti_fraud_click_id,
                'campaignId' => $tracking->projectSource->campaign_id,
                'userIP' => $tracking->user_ip,
            ];

            $existing_integration = IntegrationLog::where('provider', 'digital_ads')
                ->where('tracking_id', $tracking->id)
                ->where('event_type', 'request')
                ->where('url', 'LIKE', '%FailCallBack%')
                ->first();
            if ($existing_integration) {
                $existing_integration->updated_at = now();
                $existing_integration->save();
            }
            else {
                $integration = IntegrationLog::create([
                    'provider' => 'digital_ads',
                    'tracking_id' => $tracking->id,
                    'event_type' => 'request',
                    'status' => 'failed',
                    'payload' => $query_params,
                    'url' => $this->digitalAdsBaseUrl . 'FailCallBack',
                    'error_message' => json_encode($request->all()),
                ]);
                $response_digital_ads = Http::get($this->digitalAdsBaseUrl . 'FailCallBack?' . http_build_query($query_params));
                if ($response_digital_ads->status() == '200') {
                    $integration->status = 'success';
                    $integration->metadata = [
                        'body' => $response_digital_ads->body()
                    ];
                }
                else {
                    $integration->status = 'failed';
                    $integration->error_message = $response_digital_ads->body();
                    $integration->metadata = [
                        'body' => $response_digital_ads->body()
                    ];
                }
            }
        }
        return view('failure');
    }

    private function getMsisdnFromHeaders(Request $request)
    {
        foreach ($this->msisdnHeaders as $headerName) {
            $msisdn = $request->header($headerName);
            if ($msisdn) {
                return $msisdn;
            }
        }
        return null;
    }

    public function getRequestHeaders(Request $request)
    {
        // Get all headers from the request
        $headers = collect($request->headers->all())
            ->map(function($header) {
                return is_array($header) ? $header[0] : $header;
            })
            ->filter()
            ->toArray();

        // Convert to JSON and then to base64
        $jsonHeaders = json_encode($headers);
        $headersBase64 = base64_encode($jsonHeaders);
        $response = Response::json([
            'headersBase64' => $headersBase64,
            'msisdn' => Session::get('msisdn') // Include the stored MSISDN
        ]);
        return $response;
    }

    public function getAntiFraudScript(Request $request)
    {
//        try {
            $baseUrl = 'http://ziq-he.prime-build.co:8090/dcbprotect.php';
//            generate transaction id unique for each request
            $transactionId = uniqid('tx_', true);
//            save to session for later use
            Session::put('transaction_id', $transactionId);
            $queryParams = [
                'action' => 'script',
                'ti' => $transactionId,
                'te' => $request->te,
//                ts is the current timestamp of the transaction
                'ts' => time(),
                'servicename' => $this->config['servicename'],
                'merchantname' => 'Prime Build',
                'type' => 'he',
            ];
            Log::info("Anti-Fraud Script Request url: " . $baseUrl . '?' . http_build_query($queryParams));
//
            // Make the request
            $response = Http::get($baseUrl . '?' . http_build_query($queryParams));
////        // Check if the response is successful
           if (!$response->successful()) {

                return Response::json([
                    'success' => false,
                    'message' => 'Failed to retrieve anti-fraud script',
                ], 500);
            }
            return Response::json([
                'success' => true,
                'response' => $response->body(),
            ]);

    }

    public function savePreferredLanguage(Request $request)
    {
        $language = $request->input('language');
        // Store it in Laravel session or database
        session(['preferredLanguage' => $language]);
        return response()->json(['message' => session('preferredLanguage')]);
    }

    public function handleSubscription(Request $request)
    {
        try {
            $baseUrl = 'http://ziq-he.prime-build.co:8090/HE/oneclick/subscribeUser.php';

            // Get MSISDN from session
            $msisdn = $request->msisdn;
            $language = session('preferredLanguage');
            $tracking = Tracking::where('anti_fraud_click_id', $request->antiFrauduniqid)->first();
            // Build query parameters
            $queryParams = [
                'serviceId' => $language == 'AR' ? $this->config['serviceIdAr'] : $this->config['serviceIdKU'],
                'spId' => $this->config['spId'],
                'shortcode' => $this->config['shortcode'],
//                'msisdn' => $msisdn,
                'ClickID' => $request->antiFrauduniqid,
                'ChannelID' => $this->config['channelId'],
                'opSPID' => $this->config['opSPID'],
                'LanguageID' => $request->languageId,
                'gclid' => $tracking->click_id,
                'advertiser' => 'digitalads-google'
            ];
//            dd($queryParams);
            if ($tracking) {
                $tracking->second_click = true;
                $tracking->save();
                IntegrationLog::updateOrCreate(
                    [
                        'provider' => 'SDP',
                        'tracking_id' => $tracking->id,
                        'event_type' => 'request',
                        'status' => 'success',
                    ],
                    [
                        'payload' => $queryParams,
                        'url' => $baseUrl,

                    ]);
                // Get the full URL with parameters
                $fullUrl = $baseUrl . '?' . http_build_query($queryParams);
                $response = Response::json([
                    'success' => true,
                    'redirectUrl' => $fullUrl
                ]);
                // Return the URL to frontend
                return $response;
            }
            else {
                throw new \Exception('Tracking data not found');
            }

        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
