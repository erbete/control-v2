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

class ProcessErateUsersJob implements ShouldQueue, ShouldBeUnique
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
            $filePath = base_path('tests/data/Users.test.txt');
        } else {
            $filePath = base_path('tests/data/dumps/Users.txt');
        }

        if (!file_exists($filePath)) {
            Log::channel('stack')->error('ProcessErateUsersJob: file does not exist: ' . $filePath);
            $this->fail('ProcessErateUsersJob: file does not exist: ' . $filePath);
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
                        'company' => $row[2],
                        'first_name' => $row[3],
                        'last_name' => $row[4],
                        'street' => $row[5],
                        'address2' => $row[6],
                        'city' => $row[7],
                        'zip' => $row[8],
                        'country' => $row[9],
                        'phone1' => $row[10],
                        'phone2' => $row[11],
                        'phone3' => $row[12],
                        'fax' => $row[13],
                        'email' => $row[14],
                        'email2' => $row[15],
                        'birthday' => $row[16],
                        'username' => $row[17],
                        'personal_id' => $row[18],
                        'brand_id' => $row[19],
                    ];

                    // Process the chunks
                    if (count($buffer) >= 2000) {
                        DB::table('erate_users')->upsert($buffer, ['id']);
                        $buffer = [];
                    }
                }

                fclose($fstream);

                // Process any remaining chunks
                if (!empty($buffer)) {
                    DB::table('erate_users')->upsert($buffer, ['id']);
                }
            });

            Log::channel('stack')->info('ProcessErateUsersJob: done');
        } catch (\Throwable $e) {
            Log::channel('stack')->error('ProcessErateUsersJob: ' . $e->getMessage());
            throw $e;
        }
    }
}
