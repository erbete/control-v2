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

class ProcessErateNPExportJob implements ShouldQueue, ShouldBeUnique
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
        $filePath = base_path('tests/data/dumps/Np_export.txt');

        if (!file_exists($filePath)) {
            Log::channel('stack')->error('ProcessErateNPExportJob: file does not exist: ' . $filePath);
            $this->fail('ProcessErateNPExportJob: file does not exist: ' . $filePath);
            return;
        }

        $fstream = fopen($filePath, 'rb');
        if (!$fstream) {
            Log::channel('stack')->error('ProcessErateNPExportJob: failed to open file: ' . $filePath);
            $this->fail('ProcessErateNPExportJob: failed to open file: ' . $filePath);
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
                        'subscription_id' => $row[1],
                        'phone_number' => $row[2],
                        'order_receive_date' => $row[3],
                        'export_date' => $row[4],
                        'exact_export_time' => $row[5],
                        'customer_name' => $row[6],
                        'customer_id' => $row[7],
                        'operator_code' => $row[8],
                        'operator_descr' => $row[9],
                        'case_status_id' => $row[10],
                        'status_id' => $row[11],
                        'status' => $row[12],
                        'port_type_id' => $row[13],
                        'case_number' => $row[14],
                        'reject_code' => $row[15],
                        'reject_comment' => $row[16],
                        'platform' => 'SPNortel',
                    ];

                    // process the chunks
                    if (count($buffer) >= 2000) {
                        DB::table('erate_npexport')->upsert($buffer, ['id']);
                        $buffer = [];
                    }
                }

                fclose($fstream);

                // Process any remaining chunks
                if (!empty($buffer)) {
                    DB::table('erate_npexport')->upsert($buffer, ['id']);
                }
            });

            Log::channel('stack')->info('ProcessErateNPExportJob: done');
        } catch (\Throwable $e) {
            Log::channel('stack')->error('ProcessErateNPExportJob: ' . $e->getMessage());
            throw $e;
        }
    }
}
