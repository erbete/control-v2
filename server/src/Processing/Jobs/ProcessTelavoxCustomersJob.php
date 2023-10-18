<?php

namespace Control\Processing\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessTelavoxCustomersJob implements ShouldQueue, ShouldBeUnique
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
        $filePath = base_path('tests/data/dumps/customers.json');

        if (!file_exists($filePath)) {
            Log::channel('stack')->error('ProcessTelavoxCustomersJob: file does not exist: ' . $filePath);
            $this->fail('ProcessTelavoxCustomersJob: file does not exist: ' . $filePath);
            return;
        }

        $fstring = file_get_contents($filePath);
        if (!$fstring) {
            Log::channel('stack')->error('ProcessTelavoxCustomersJob: failed to read file: ' . $filePath);
            $this->fail('ProcessTelavoxCustomersJob: failed to read file: ' . $filePath);
            return;
        }

        $fileData = json_decode($fstring, true);
        if (is_null($fileData)) {
            Log::channel('stack')->error('ProcessTelavoxCustomersJob: failed to read file: ' . $filePath);
            $this->fail('ProcessTelavoxCustomersJob: failed to read file: ' . $filePath);
            return;
        }

        try {
            DB::transaction(function () use ($fileData) {
                collect($fileData)
                    ->map(function (array $object) {
                        // Map values
                        $data = [
                            'id' => $object['customerid'],
                            'seller_id' => $object['sellerid'],
                            'company_name' => $object['companyname'],
                            'reg_num' => $object['regnr'],
                            'street' => $object['street'],
                            'email' => $object['email'],
                            'created' => $object['created'],
                            'inactivation_time' => $object['inactivationtime'],
                            'note' => $object['note'],
                            'seller_name' => $object['sellername'],
                            'deal_num' => $object['dealnr'],
                            'paytime' => $object['paytime'],
                        ];

                        // Remove any new lines from the note field
                        $data['note'] = trim(preg_replace('/\s\s+/', ' ', $data['note']));

                        // Trim whitespaces, and change any empty/blank string to null
                        foreach ($data as &$field) {
                            if (is_null($field)) continue;
                            $field = trim($field);
                            if (empty($field)) $field = null;
                        }

                        return $data;
                    })
                    ->chunk(2000)
                    ->each(function (Collection $chunk) {
                        DB::table('telavox_customers')->upsert($chunk->toArray(), ['id']);
                    });
            });

            Log::channel('stack')->info('ProcessTelavoxCustomersJob: done');
        } catch (\Throwable $e) {
            Log::channel('stack')->error('ProcessTelavoxCustomersJob: ' . $e->getMessage());
            throw $e;
        }
    }
}
