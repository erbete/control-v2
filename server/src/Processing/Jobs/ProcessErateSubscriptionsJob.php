<?php

namespace Control\Processing\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessErateSubscriptionsJob implements ShouldQueue, ShouldBeUnique
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // TODO
        if (App::environment() === 'testing') {
            $filePath = base_path('tests/data/Subscription.test.txt');
        } else {
            $filePath = base_path('tests/data/dumps/Subscription.txt');
        }

        if (!file_exists($filePath)) {
            Log::channel('stack')->error('ProcessErateSubscriptionsJob: file does not exist: ' . $filePath);
            $this->fail('ProcessErateSubscriptionsJob: file does not exist: ' . $filePath);
            return;
        }

        $fstream = fopen($filePath, 'rb');
        if (!$fstream) {
            Log::channel('stack')->error('ProcessErateSubscriptionsJob: failed to open file: ' . $filePath);
            $this->fail('ProcessErateSubscriptionsJob: failed to open file: ' . $filePath);
            return;
        }

        try {
            DB::transaction(function () use ($fstream) {
                // Skip the CSV header
                fgetcsv($fstream, 0, ';');

                $buffer = [];

                while (($row = fgetcsv($fstream, 0, ';')) !== false) {
                    // Ignore blank rows
                    if ($row === [null]) continue;

                    // Trim whitespaces, and change any empty/blank string to null
                    foreach ($row as &$field) {
                        if (is_null($field)) continue;
                        $field = trim($field);
                        if (empty($field)) $field = null;
                    }

                    // Map the data
                    $buffer[] =  [
                        'id' => $row[0],
                        'account_id' => $row[1],
                        'owner_id' => $row[2],
                        'product_description' => $row[3],
                        'phone_number' => $row[4],
                        'establish_date' => $row[5],
                        'start_date' => $row[6],
                        'end_date' => $row[7],
                        'lockin_start_date' => $row[8],
                        'lockin_end_date' => $row[9],
                        'lockin_length' => $row[10],
                        'status_id' => $row[11],
                        'sales_store' => $row[12],
                        'sales_rep' => $row[13],
                        'brand_id' => $row[14],
                        'service_status' => $row[15],
                        'imsi' => $row[16],
                        'port_date' => $row[17],
                        'last_logged_in' => $row[18],
                        'platform' => $row[19],
                        'order_id' => $row[20],
                        'entered_by' => $row[21],
                    ];

                    // Process the chunks
                    if (count($buffer) >= 2000) {
                        DB::table('erate_subscriptions')->upsert($buffer, ['id']);
                        $buffer = [];
                    }
                }

                fclose($fstream);

                // Process any remaining chunks
                if (!empty($buffer)) {
                    DB::table('erate_subscriptions')->upsert($buffer, ['id']);
                }
            });

            Log::channel('stack')->info('ProcessErateSubscriptionsJob: done');
        } catch (\Throwable $e) {
            Log::channel('stack')->error('ProcessErateSubscriptionsJob: ' . $e->getMessage());
            throw $e;
        }
    }
}
