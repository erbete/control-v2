<?php

namespace Control\Processing\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use Control\Infrastructure\Subscription;
use Control\Infrastructure\SubscriptionStatus;

class ProcessSubscriptionsJob implements ShouldQueue, ShouldBeUnique
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
                DB::table('subscriptions')->delete();

                $subOwners = DB::table('erate_users')
                    ->get([
                        'id',
                        'first_name',
                        'last_name',
                    ])
                    ->mapWithKeys(fn ($data) => [$data->id => $data])
                    ->toArray();

                $batch = [];
                DB::table('accounts', 'a')
                    ->leftJoin('erate_subscriptions AS es', 'es.account_id', '=', 'a.id')
                    ->get([
                        'es.id AS subscription_id',
                        'es.phone_number',
                        'es.establish_date',
                        'es.start_date',
                        'es.lockin_end_date',
                        'es.product_description',
                        'es.service_status',
                        'a.id AS account_id',
                        'es.owner_id',
                        'es.imsi',
                    ])
                    ->each(function ($data) use (&$subOwners, &$batch) {
                        if (is_null($data->subscription_id)) {
                            Log::channel('stack')->warning('ProcessSubscriptionsJob: found no subscription(s) for account #' . $data->account_id);
                            return;
                        }

                        $subOwner = $subOwners[$data->owner_id];
                        $batch[] = [
                            'id' => (int)$data->subscription_id,
                            'phone_number' => $data->phone_number,
                            'first_name' => $subOwner->first_name,
                            'last_name' => $subOwner->last_name,
                            'establish_date' => $data->establish_date,
                            'delivery_date' => $data->start_date,
                            'lockin_end_date' => $data->lockin_end_date,
                            'description' => $data->product_description,
                            'status' => SubscriptionStatus::from($data->service_status)->name,
                            'account_id' => (int)$data->account_id,
                            'imsi' => $data->imsi,
                        ];

                        if (count($batch) >= 2000) {
                            Subscription::insert($batch);
                            $batch = [];
                        }
                    });

                if (!empty($batch)) {
                    Subscription::insert($batch);
                }
            });

            Log::channel('stack')->info('ProcessSubscriptionsJob: done');
        } catch (\Throwable $e) {
            Log::channel('stack')->error('ProcessSubscriptionsJob: ' . $e->getMessage());
            throw $e;
        }
    }
}
