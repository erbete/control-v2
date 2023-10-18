<?php

namespace Control\Rebinding\Actions;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

use Control\Rebinding\Mappers\RebindingAccountsMapper;
use Control\Rebinding\RebindingStatus;

class RebindingGetAccountsAction
{
    public function execute(
        ?string $lockinFromMonth,
        ?string $lockinToMonth,
        ?string $statuses,
        ?string $periods,
        int $perPage,
        int $page
    ): array {
        $bindings = [];
        $offset = ($page - 1) * $perPage;
        $key = 'rebindingTotalAccounts:' . $lockinFromMonth . $lockinToMonth . $statuses;
        if ($statuses) {
            $statuses = explode(',', strtoupper($statuses));
        } else {
            $statuses = [];
        }

        $lockinStartDateCondition = $this->getLockinStartDateCondition($lockinFromMonth, $bindings);
        $lockinEndDateCondition = $this->getLockinEndDateCondition($lockinToMonth, $bindings);
        $relevantAccountsCte = $this->getRelevantAccountsCte($statuses, $lockinStartDateCondition, $lockinEndDateCondition, $perPage, $offset, $bindings);
        $periodCondition = $this->getPeriodCondition($periods, $bindings);
        $query = $this->buildMainQuery($relevantAccountsCte, $periodCondition);

        $totalAccounts = Cache::remember($key, 3600 * 8, function () use ($lockinFromMonth, $lockinToMonth, $statuses) {
            return $this->getTotalAccounts($lockinFromMonth, $lockinToMonth, $statuses);
        });

        return [
            'page' => $page,
            'totalPages' => ceil($totalAccounts / $perPage),
            'count' => $totalAccounts,
            'perPage' => $perPage,
            'data' => RebindingAccountsMapper::map(DB::select($query, $bindings)),
        ];
    }

    private function getTotalAccounts(?string $lockinFromMonth, ?string $lockinToMonth, array $statuses): int
    {
        $totalAccounts = DB::table('rebinding_account_usages', 'rau')
            ->leftJoin('rebinding_activities AS ra', 'ra.account_id', '=', 'rau.account_id');

        if ($lockinFromMonth) {
            $totalAccounts = $totalAccounts->whereDate('lockin_end_date', '>=', $lockinFromMonth);
        }

        if ($lockinToMonth) {
            $totalAccounts = $totalAccounts->whereDate('lockin_end_date', '<=', $lockinToMonth);
        }

        $accounts = $totalAccounts
            ->select([
                'rau.account_id',
                DB::raw("COALESCE(ra.status, 'NONE') AS status")
            ])
            ->distinct()
            ->get();

        // Goto RebindingController@setStatus for explanation of why we are not counting rebinded customers
        return count(array_filter($accounts->toArray(), function ($acc) use ($statuses) {
            return ($acc->status !== RebindingStatus::REBINDED->value) &&
                (empty($statuses) || in_array($acc->status, $statuses));
        }));
    }

    private function getLockinStartDateCondition(?string $lockinFromMonth, array &$bindings): string
    {
        if (!$lockinFromMonth) return '';

        try {
            $lockinStartDate = Carbon::createFromFormat('Y-m-d', trim($lockinFromMonth))->format('Y-m-d');
            $bindings[] = $lockinStartDate;
            return 'lockin_end_date >= ?';
        } catch (Exception $e) {
            return '';
        }
    }

    private function getLockinEndDateCondition(?string $lockinToMonth, array &$bindings): string
    {
        if (!$lockinToMonth) return '';

        try {
            $lockinEndDate = Carbon::createFromFormat('Y-m-d', trim($lockinToMonth))->format('Y-m-d');
            $bindings[] = $lockinEndDate;
            return 'lockin_end_date <= ?';
        } catch (Exception $e) {
            return '';
        }
    }

    private function getRelevantAccountsCte(array $statuses, string $lockinStartDateCondition, string $lockinEndDateCondition, int $perPage, int $offset, array &$bindings): string
    {
        if (count($statuses) > 0) {
            $placeholders = implode(',', array_fill(0, count($statuses), '?'));
            $bindings = array_merge($bindings, $statuses);

            return "
            WITH relevant_accounts AS (
                SELECT
                    rau.account_id,
                    MIN(rau.lockin_end_date) AS oldest_lockin_end_date,
                    rea.status
                FROM rebinding_account_usages rau
                LEFT JOIN rebinding_activities rea ON rea.account_id = rau.account_id
                WHERE 1 = 1
                " . ($lockinStartDateCondition ? " AND $lockinStartDateCondition " : "") . "
                " . ($lockinEndDateCondition ? " AND $lockinEndDateCondition " : "") . "
                AND (
                    CASE
                        WHEN rea.status IS NULL THEN 'NONE'
                        ELSE rea.status
                    END
                ) IN ($placeholders)
                GROUP BY rau.account_id, rea.status
                LIMIT $perPage OFFSET $offset
            )";
        }

        return "
        WITH relevant_accounts AS (
            SELECT
                account_id,
                MIN(lockin_end_date) AS oldest_lockin_end_date
            FROM rebinding_account_usages
            WHERE 1 = 1
            " . ($lockinStartDateCondition ? " AND $lockinStartDateCondition " : "") . "
            " . ($lockinEndDateCondition ? " AND $lockinEndDateCondition " : "") . "
            GROUP BY account_id
            LIMIT $perPage OFFSET $offset
        )";
    }

    private function getPeriodCondition(?string $periods, array &$bindings): string
    {
        $parsedPeriods = [];
        if (!$periods) return '';

        foreach (explode(',', $periods) as $period) {
            try {
                $parsedPeriods[] = Carbon::createFromFormat('Ym', $period)->format('Ym');
            } catch (Exception $e) {
                continue;
            }
        }

        if (empty($parsedPeriods)) return '';

        $placeholders = implode(',', array_fill(0, count($parsedPeriods), '?'));
        $bindings = array_merge($bindings, $parsedPeriods);

        return "AND usage_item ->> 'period' IN ($placeholders)";
    }

    private function buildMainQuery(string $relevantAccountsCte, string $periodCondition): string
    {
        return "
            $relevantAccountsCte,
            filtered_usages AS (
                SELECT
                    rau.account_id,
                    jsonb_agg(usage_item) AS usages
                FROM
                    rebinding_account_usages rau,
                    jsonb_array_elements(rau.usages) AS usage_item
                WHERE rau.account_id IN (SELECT account_id FROM relevant_accounts)
                $periodCondition
                GROUP BY rau.account_id
            )
            SELECT
                ra.account_id,
                rac.company,
                rac.contact,
                rac.sales_rep,
                rac.sales_store,
                rac.active_subs_count,
                ra.oldest_lockin_end_date,
                COALESCE(rea.status, 'NONE') AS status,
                rea.note,
                u.name,
                fu.usages
            FROM rebinding_accounts rac
            JOIN relevant_accounts ra ON ra.account_id = rac.account_id
            LEFT JOIN filtered_usages fu ON fu.account_id = rac.account_id
            LEFT JOIN rebinding_activities rea ON rea.account_id = rac.account_id
            LEFT JOIN users u ON u.id = rea.user_id;
        ";
    }
}
