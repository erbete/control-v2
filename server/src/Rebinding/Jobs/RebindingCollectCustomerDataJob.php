<?php

namespace Control\Rebinding\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use Control\Rebinding\RebindingStatus;
use Control\Infrastructure\RebindingActivity;

class RebindingCollectCustomerDataJob implements ShouldQueue, ShouldBeUnique
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
            DB::statement('DROP MATERIALIZED VIEW IF EXISTS rebinding_accounts');
            DB::statement("
                CREATE MATERIALIZED VIEW rebinding_accounts AS
                WITH active_subs_count AS (
                    SELECT s.account_id, COUNT(*) AS active_subs_count
                    FROM subscriptions s
                    WHERE s.description NOT IN ('Nortel Mitel Fastnett', 'Nortel Mitel', 'Nortel Multisim Data')
                    AND s.status IN ('IMPORTING', 'ACTIVE')
                    GROUP BY s.account_id
                )
                SELECT
                    s.account_id,
                    ao.first_name || ' ' || ao.last_name AS contact,
                    ao.company,
                    a.sales_rep,
                    a.sales_store,
                    asub.active_subs_count
                FROM subscriptions s
                LEFT JOIN customer_expenditures ce ON ce.subscription_id = s.id
                LEFT JOIN accounts a ON a.id = s.account_id
                LEFT JOIN account_owners ao ON ao.id = a.account_owner_id
                JOIN active_subs_count asub ON asub.account_id = s.account_id
                WHERE s.description IN ('Nortel Mobil Telenor', 'Atea Mobil Telenor', 'Nortel Mobil MVNO')
                AND s.lockin_end_date IS NOT NULL
                AND s.status IN ('IMPORTING', 'ACTIVE')
                GROUP BY
                    s.account_id,
                    asub.active_subs_count,
                    ao.first_name,
                    ao.last_name,
                    ao.company,
                    a.sales_rep,
                    a.sales_store
            ");

            DB::statement('DROP MATERIALIZED VIEW IF EXISTS rebinding_account_usages');
            DB::statement("
                CREATE MATERIALIZED VIEW rebinding_account_usages AS
                SELECT
                    s.phone_number,
                    s.lockin_end_date,
                    COALESCE(jsonb_agg(ce.usage) FILTER (WHERE ce.usage IS NOT NULL), '[]') AS usages,
                    a.id AS account_id
                FROM customer_expenditures ce
                LEFT JOIN subscriptions s ON s.id = ce.subscription_id
                LEFT JOIN accounts a ON a.id = s.account_id
                WHERE s.description IN ('Nortel Mobil Telenor', 'Atea Mobil Telenor', 'Nortel Mobil MVNO')
                AND s.lockin_end_date IS NOT NULL
                AND s.status IN ('IMPORTING', 'ACTIVE')
                GROUP BY
                    s.phone_number,
                    s.lockin_end_date,
                    a.id
            ");

            // Goto RebindingController@setStatus for explanation of why we are resetting rebinded customers
            RebindingActivity::query()
                ->where('status', RebindingStatus::REBINDED->value)
                ->whereDate('updated_at', '<=', Carbon::now()->subWeek())
                ->delete();

            Log::channel('stack')->info('RebindingArrangeCompanyDataJob: done');
        } catch (\Throwable $e) {
            Log::channel('stack')->error('RebindingArrangeCompanyDataJob: ' . $e->getMessage());
            throw $e;
        }
    }
}
