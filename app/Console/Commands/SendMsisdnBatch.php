<?php

namespace App\Console\Commands;

use App\Models\Tracking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendMsisdnBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'msisdn:send-batch';
    protected $description = 'Send MSISDNs in bulk to the uploader API';


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $msisdns = [
            "9647745343187",
            "9647779830537",
            "9647779194019",
            "9647736553739",
            "9647716798673",
            "9647721348887",
            "9647744938474",
            "9647702011225",
            "9647729704483",
            "9647768161221",
            "9647702909099",
            "9647763265359",
            "9647702909099",
            "9647768194044",
            "9647723562893",
            "9647703945990",
            "9647758398251",
            "9647768654971",
            "9647739615464",
            "9647746969971",
            "9647721025578",
            "9647780148935",
            "9647735395609",
            "9647717608188",
            "9647730310849",
            "9647715507190",
            "9647702436393",
            "9647723689336",
            "9647733754624",
            "9647717082707",
            "9647700182968",
            "9647719784757",
            "9647710267772",
        ];



        foreach ($msisdns as $msisdn) {
            $tracking = Tracking::where('msisdn', $msisdn)
                ->where('success', 0)->where('first_click', 1)
                ->where('second_click', 1)
                ->where('second_page_visit', 1)
                ->first();

            if ($tracking) {
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
                        $tracking->update(['success' => 1, 'failure' => 0]);
                        $this->info("Uploaded successfully for MSISDN: $msisdn");
                    } else {
                        Log::error('Upload failed', [
                            'msisdn' => $msisdn,
                            'status' => $response->status(),
                            'response' => $response->body()
                        ]);
                        $this->error("Failed upload for $msisdn");
                    }
                } catch (\Exception $e) {
                    Log::error('Exception during upload', [
                        'msisdn' => $msisdn,
                        'error' => $e->getMessage()
                    ]);
                    $this->error("Exception for $msisdn: " . $e->getMessage());
                }
            } else {
                $this->warn("No eligible record for: $msisdn");
            }
        }
//
//        $this->info('Batch MSISDN processing completed.');
//        $this->info("Scanning for duplicated MSISDNs with success = 1...");
//
//        // Step 1: Get all duplicate MSISDNs where count > 1
//        $duplicates = Tracking::select('msisdn')
//            ->where('success', 1)
//            ->groupBy('msisdn')
//            ->havingRaw('COUNT(*) > 1')
//            ->pluck('msisdn');
//
//        $this->info("Found " . $duplicates->count() . " duplicated MSISDN(s)");
//
//        foreach ($duplicates as $msisdn) {
//            // Step 2: Keep the first one, delete the rest
//            $records = Tracking::where('msisdn', $msisdn)
//                ->where('success', 1)
//                ->orderBy('id')
//                ->get();
//
//            $recordsToDelete = $records->slice(1); // Skip the first one
//
//            $deletedCount = Tracking::whereIn('id', $recordsToDelete->pluck('id'))->delete();
//
//            $this->line("MSISDN: $msisdn — Removed $deletedCount duplicate(s)");
//        }
//
//        $this->info("Cleanup complete.");
//        $this->info("Cleaning MSISDNs: keeping only success=1 rows...");
//
//        // Step 1: Get all MSISDNs that have at least one success = 1
//        $msisdns = Tracking::where('success', 1)
//            ->groupBy('msisdn')
//            ->pluck('msisdn');
//
//        $this->info("Found " . $msisdns->count() . " MSISDN(s) to clean");
//
//        foreach ($msisdns as $msisdn) {
//            // Delete all records for this MSISDN where success != 1
//            $deletedCount = Tracking::where('msisdn', $msisdn)
//                ->where('success', '!=', 1)
//                ->delete();
//
//            if ($deletedCount > 0) {
//                $this->line("Deleted $deletedCount record(s) for MSISDN: $msisdn");
//            }
//        }
//
//        $this->info("Done. All non-success records removed for MSISDNs with success=1.");
    }
}
