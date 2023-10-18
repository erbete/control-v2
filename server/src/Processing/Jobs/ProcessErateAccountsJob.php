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

class ProcessErateAccountsJob implements ShouldQueue, ShouldBeUnique
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
            $filePath = base_path('tests/data/Account.test.txt');
        } else {
            $filePath = base_path('tests/data/dumps/Account.txt');
        }

        if (!file_exists($filePath)) {
            Log::channel('stack')->error('ProcessErateAccountsJob: file does not exist: ' . $filePath);
            $this->fail('ProcessErateAccountsJob: file does not exist: ' . $filePath);
            return;
        }

        $fstream = fopen($filePath, 'rb');
        if (!$fstream) {
            Log::channel('stack')->error('ProcessErateAccountsJob: failed to open file: ' . $filePath);
            $this->fail('ProcessErateAccountsJob: failed to open file: ' . $filePath);
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

                    // map the data
                    $buffer[] =  [
                        'id' => $row[0],
                        'account_type' => boolval($row[1]),
                        'owner_id' => $row[2],
                        'customer_number' => $row[3],
                        'referenced_by' => $row[4],
                        'sales_rep' => $row[5],
                        'brand_id' => $row[6],
                        'owner_alert_mobile' => $row[7],
                    ];

                    // process the chunks
                    if (count($buffer) >= 2000) {
                        DB::table('erate_accounts')->upsert($buffer, ['id']);
                        $buffer = [];
                    }
                }

                fclose($fstream);

                // Process any remaining chunks
                if (!empty($buffer)) {
                    DB::table('erate_accounts')->upsert($buffer, ['id']);
                }
            });

            Log::channel('stack')->info('ProcessErateAccountsJob: done');
        } catch (\Throwable $e) {
            Log::channel('stack')->error('ProcessErateAccountsJob: ' . $e->getMessage());
            throw $e;
        }
    }
}
