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

class ProcessTelavoxUsersJob implements ShouldQueue, ShouldBeUnique
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
        $filePath = base_path('tests/data/dumps/users.json');

        if (!file_exists($filePath)) {
            Log::channel('stack')->error('ProcessTelavoxUsersJob: file does not exist: ' . $filePath);
            $this->fail('ProcessTelavoxUsersJob: file does not exist: ' . $filePath);
            return;
        }

        $fstring = file_get_contents($filePath);
        if (!$fstring) {
            Log::channel('stack')->error('ProcessTelavoxUsersJob: failed to read file: ' . $filePath);
            $this->fail('ProcessTelavoxUsersJob: failed to read file: ' . $filePath);
            return;
        }

        $fileData = json_decode($fstring, true);
        if (is_null($fileData)) {
            Log::channel('stack')->error('ProcessTelavoxUsersJob: failed to read file: ' . $filePath);
            $this->fail('ProcessTelavoxUsersJob: failed to read file: ' . $filePath);
            return;
        }

        try {
            DB::transaction(function () use ($fileData) {
                collect($fileData)
                    ->map(function (array $object) {
                        // Map the data
                        $data = [
                            'id' => $object['userid'],
                            'customer_id' => $object['customerid'],
                            'account_id' => $object['accountid'],
                            'email' => $object['email'],
                            'name' => $object['name'],
                            'street' => $object['street'],
                            'postal_code' => $object['postnr'],
                        ];

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
                        DB::table('telavox_users')->upsert($chunk->toArray(), ['id']);
                    });
            });

            Log::channel('stack')->info('ProcessTelavoxUsersJob: done');
        } catch (\Throwable $e) {
            Log::channel('stack')->error('ProcessTelavoxUsersJob: ' . $e->getMessage());
            throw $e;
        }
    }
}
