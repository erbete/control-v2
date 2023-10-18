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

use Control\Infrastructure\Account;

class ProcessAccountsJob implements ShouldQueue, ShouldBeUnique
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
                DB::table('accounts')->delete();

                $saleReps = DB::table('erate_subscriptions')
                    ->get([
                        'account_id',
                        'sales_rep',
                        'sales_store'
                    ])
                    ->mapWithKeys(fn ($value) => [$value->account_id => $value]);

                DB::table('erate_accounts', 'ea')
                    ->select([
                        'ea.id AS erate_account_id',
                        'ao.id AS account_owner_id',
                    ])
                    ->leftJoin('account_owners AS ao', 'ao.id', '=', 'ea.owner_id')
                    ->orderBy('ea.id')
                    ->chunk(2000, function ($chunks) use(&$saleReps) {
                        $batch = [];
                        foreach ($chunks as $chunk) {
                            $batch[] = [
                                'id' => (int)$chunk->erate_account_id,
                                'sales_rep' => $saleReps[$chunk->erate_account_id]->sales_rep ?? null,
                                'sales_store' => $saleReps[$chunk->erate_account_id]->sales_store ?? null,
                                'account_owner_id' => (int)$chunk->account_owner_id,
                            ];
                        }

                        Account::insert($batch);
                    });
            });

            Log::channel('stack')->info('ProcessAccountsJob: done');
        } catch (\Throwable $e) {
            Log::channel('stack')->error('ProcessAccountsJob: ' . $e->getMessage());
            throw $e;
        }
    }
}
