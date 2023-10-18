<?php

namespace Control\Processing\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Control\Infrastructure\CustomerExpenditure;

class ProcessCustomerExpendituresJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        try {
            DB::transaction(function () {
                DB::table('customer_expenditures')->delete(); // cannot use ->truncate inside a transaction
                DB::statement('ALTER SEQUENCE customer_expenditures_id_seq RESTART WITH 1'); // reset pkey back to 1

                DB::table('erate_customer_revenue_figs', 'ecrf')
                    ->join('subscriptions AS s', 'ecrf.service_imsi', '=', 's.imsi')
                    ->select([
                        's.id AS subscription_id',
                        'ecrf.priceplan',
                        'ecrf.total_revenue',
                        'ecrf.subscription_fees_revenue',
                        'ecrf.total_traffic_cost',
                        'ecrf.voice_national_seconds',
                        'ecrf.total_bytes_national',
                        'ecrf.period',
                        's.imsi',
                    ])
                    ->orderByDesc('s.id')
                    ->chunk(2000, function ($chunks) {
                        $batch = [];
                        foreach ($chunks as $chunk) {
                            $usage = json_encode([
                                'priceplan' => (string)$chunk->priceplan,
                                'total_revenue' => (string)$chunk->total_revenue,
                                'subscription_fees_revenue' => (string)$chunk->subscription_fees_revenue,
                                'total_traffic_cost' => (string)$chunk->total_traffic_cost,
                                'voice_national_seconds' => (string)$chunk->voice_national_seconds,
                                'total_bytes_national' => (string)$chunk->total_bytes_national,
                                'period' => (string)$chunk->period,
                            ], JSON_UNESCAPED_UNICODE);

                            $batch[] = [
                                'usage' => $usage,
                                'source' => 'ERATE',
                                'subscription_id' => $chunk->subscription_id,
                            ];
                        }

                        CustomerExpenditure::insert($batch);
                    });
            });

            Log::channel('stack')->info('ProcessCustomerExpendituresJob: done');
        } catch (\Throwable $e) {
            Log::channel('stack')->error('ProcessCustomerExpendituresJob: ' . $e->getMessage());
            throw $e;
        }
    }
}
