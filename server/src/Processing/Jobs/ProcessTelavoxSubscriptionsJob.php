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
use Illuminate\Support\Str;

class ProcessTelavoxSubscriptionsJob implements ShouldQueue, ShouldBeUnique
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
        $filePath = base_path('tests/data/dumps/subscriptions.json');

        if (!file_exists($filePath)) {
            Log::channel('stack')->error('ProcessTelavoxSubscriptionsJob: file does not exist: ' . $filePath);
            $this->fail('ProcessTelavoxSubscriptionsJob: file does not exist: ' . $filePath);
            return;
        }

        $fstring = file_get_contents($filePath);
        if (!$fstring) {
            Log::channel('stack')->error('ProcessTelavoxSubscriptionsJob: failed to read file: ' . $filePath);
            $this->fail('ProcessTelavoxSubscriptionsJob: failed to read file: ' . $filePath);
            return;
        }

        $fileData = json_decode($fstring, true);
        if (is_null($fileData)) {
            Log::channel('stack')->error('ProcessTelavoxSubscriptionsJob: failed to read file: ' . $filePath);
            $this->fail('ProcessTelavoxSubscriptionsJob: failed to read file: ' . $filePath);
            return;
        }

        try {
            DB::transaction(function () use ($fileData) {
                collect($fileData)
                    ->map(function (array $object) {
                        // Map the data
                        $data = [
                            'id' => $object['accountsubscriptionid'],
                            'customer_id' => $object['customerid'],
                            'account_id' => $object['accountid'],
                            'user_id' => $object['userid'],
                            'retailer_subscription_id' => $object['retailersubscriptionid'],
                            'subscription_id' => $object['subscriptionid'],
                            'customer_month_cost' => $object['customer_monthcost'],
                            'customer_one_time_cost' => $object['customer_onetimecost'],
                            'retailer_month_cost' => $object['retailer_monthcost'],
                            'retailer_one_time_cost' => $object['retailer_onetimecost'],
                            'delivery_date' => $object['deliverydate'],
                            'anum' => $object['anum'],
                            'description' => $object['description'],
                            'notify_cancel_time' => $object['notifycanceltime'],
                            'inactivation_time' => $object['inactivationtime'],
                            'mobile_anumber' => $object['mobile_anumber'],
                            'fixed_anumber' => $object['fixed_anumber'],
                            'added_date' => $object['addeddate'],
                            'brand' => $object['brand'],
                            'model' => $object['model'],
                        ];

                        // Trim whitespaces, and change any empty/blank string to null
                        foreach ($data as &$field) {
                            if (is_null($field)) continue;
                            $field = trim($field);
                            if (empty($field)) $field = null;
                        }

                        // Remove junk from phone numbers
                        if (!is_null($data['anum'])) {
                            $data['anum'] = $this->sanitizePhoneNumber($data['anum']);
                        }

                        if (!is_null($data['mobile_anumber'])) {
                            $data['mobile_anumber'] = $this->sanitizePhoneNumber($data['mobile_anumber']);
                        }

                        if (!is_null($data['fixed_anumber'])) {
                            $data['fixed_anumber'] = $this->sanitizePhoneNumber($data['fixed_anumber']);
                        }

                        // Apply default values
                        if (is_null($data['customer_month_cost'])) $data['customer_month_cost'] = 0;
                        if (is_null($data['customer_one_time_cost'])) $data['customer_one_time_cost'] = 0;
                        if (is_null($data['retailer_month_cost'])) $data['retailer_month_cost'] = 0;
                        if (is_null($data['retailer_one_time_cost'])) $data['retailer_one_time_cost'] = 0;

                        return $data;
                    })
                    ->chunk(2000)
                    ->each(function (Collection $chunk) {
                        DB::table('telavox_subscriptions')->upsert($chunk->toArray(), ['id']);
                    });
            });

            Log::channel('stack')->info('ProcessTelavoxSubscriptionsJob: done');
        } catch (\Throwable $e) {
            Log::channel('stack')->error('ProcessTelavoxSubscriptionsJob: ' . $e->getMessage());
            throw $e;
        }
    }

    private function sanitizePhoneNumber(string $number)
    {
        if (Str::startsWith($number, '0047')) {
            return Str::substr($number, 4);
        }

        if (Str::startsWith($number, '0046')) {
            return Str::substr($number, 4);
        }

        if (Str::length($number) === 17) {
            return Str::substr($number, 7, 10);
        }

        if (Str::length($number) === 19) {
            return Str::substr($number, 11, 8);
        }

        if (Str::length($number) === 23) {
            return Str::substr($number, 11, 12);
        }

        return $number;
    }
}
