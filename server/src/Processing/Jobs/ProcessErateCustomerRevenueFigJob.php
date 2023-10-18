<?php

namespace Control\Processing\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessErateCustomerRevenueFigJob implements ShouldQueue, ShouldBeUnique
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
        $filePath = base_path('tests/data/dumps/Customer_revenue_fig.txt');

        if (!file_exists($filePath)) {
            Log::channel('stack')->error('ProcessErateCustomerRevenueFigJob: file does not exist: ' . $filePath);
            $this->fail('ProcessErateCustomerRevenueFigJob: file does not exist: ' . $filePath);
            return;
        }

        $fstream = fopen($filePath, 'rb');
        if (!$fstream) {
            Log::channel('stack')->error('ProcessErateCustomerRevenueJob: failed to open file: ' . $filePath);
            $this->fail('ProcessErateCustomerRevenueJob: failed to open file: ' . $filePath);
            return;
        }

        try {
            DB::transaction(function () use ($fstream) {
                // Skip the CSV header
                fgetcsv($fstream, 0, ';');

                $buffer = [];

                while (($row = fgetcsv($fstream, 0, ';')) !== false) {
                    // Ignore blank lines
                    if ($row === [null]) continue;

                    // Trim whitespaces, and change any empty/blank string to null
                    foreach ($row as &$field) {
                        if (is_null($field)) continue;
                        $field = trim($field);
                        if (empty($field)) $field = null;
                    }

                    // Map the data
                    $buffer[] =  [
                        'id' => hash('sha3-256', implode('', $row)),
                        'period' => $row[0],
                        'network' => $row[1],
                        'service_status' => $row[2],
                        'phone_number' => $row[3],
                        'service_imsi' => $row[4],
                        'subscription_status' => $row[5],
                        'subscription_start_date' => $row[6],
                        'subscription_end_date' => $row[7],
                        'rate_plan_description' => $row[8],
                        'u2_company' => $row[9],
                        'u2_first_name' => $row[10],
                        'u2_last_name' => $row[11],
                        'priceplan' => $row[12],
                        'priceplan_chg_last_mon' => ($row[13] === null || $row[13] === 'N') ? false : true,
                        'total_revenue' => (float)($row[14] ? str_replace(',', '.', $row[14]) : 0),
                        'traffic_revenue' => (float)($row[15] ? str_replace(',', '.', $row[15]) : 0),
                        'subscription_fees_revenue' => (float)($row[16] ? str_replace(',', '.', $row[16]) : 0),
                        'other_fees_revenue' => (float)($row[17] ? str_replace(',', '.', $row[17]) : 0),
                        'total_traffic_cost' => (float)($row[18] ? str_replace(',', '.', $row[18]) : 0),
                        'traffic_cost_inside_bundle' => (float)($row[19] ? str_replace(',', '.', $row[19]) : 0),
                        'traffic_cost_outside_bundle' => (float)($row[20] ? str_replace(',', '.', $row[20]) : 0),
                        'simcard_total_cost' => (float)($row[21] ? str_replace(',', '.', $row[21]) : 0),
                        'mbb_total_cost' => (float)($row[22] ? str_replace(',', '.', $row[22]) : 0),
                        'datacard_total_cost' => (float)($row[23] ? str_replace(',', '.', $row[23]) : 0),
                        'twin_total_cost' => (float)($row[24] ? str_replace(',', '.', $row[24]) : 0),
                        'sms_national_count' => (int)$row[25],
                        'voice_national_seconds' => (int)$row[26],
                        'total_bytes_national' => (int)$row[27],
                    ];

                    // process the chunks
                    if (count($buffer) >= 2000) {
                        DB::table('erate_customer_revenue_figs')->upsert($buffer, ['id']);
                        $buffer = [];
                    }
                }

                fclose($fstream);

                // Process any remaining chunks
                if (!empty($buffer)) {
                    DB::table('erate_customer_revenue_figs')->upsert($buffer, ['id']);
                }
            });

            Log::channel('stack')->info('ProcessErateCustomerRevenueJob: done');
        } catch (\Throwable $e) {
            Log::channel('stack')->error('ProcessErateCustomerRevenueJob: ' . $e->getMessage());
            throw $e;
        }
    }
}
