<?php

namespace Control\Rebinding\Mappers;

use Control\Rebinding\RebindingStatus;

class RebindingAccountsMapper
{
    public static function map(array $accounts): array
    {
        $data = [];

        foreach ($accounts as $account) {
            // Goto RebindingController@setStatus for explanation of why we are exluding rebinded customers
            if ($account->status === RebindingStatus::REBINDED->value) continue;

            $accountId = $account->account_id;

            $data[$accountId] ??= [
                'accountId' => (string)$accountId,
                'company' => $account->company,
                'contact' => $account->contact,
                'salesRep' => $account->sales_rep,
                'salesStore' => $account->sales_store,
                'user' => $account->name,
                'status' => $account->status,
                'note' => $account->note,
                'activeSubsCount' => (string)$account->active_subs_count,
                'oldestLockinEndDate' => $account->oldest_lockin_end_date,
                'revenue' => [],
            ];

            if (!$account->usages) continue;

            foreach (json_decode($account->usages, true, JSON_UNESCAPED_UNICODE) as $usage) {
                $companyData = &$data[$accountId];
                $period = $usage['period'];
                $companyData['revenue'][$period] ??= [
                    'totalRevenue' => 0,
                    'totalTrafficCost' => 0,
                    'totalVoice' => 0,
                    'coverageRatio' => 0,
                ];

                $revenuePeriod = &$companyData['revenue'][$period];
                $revenuePeriod['totalRevenue'] += (float)$usage['total_revenue'];
                $revenuePeriod['totalTrafficCost'] += (float)$usage['total_traffic_cost'];
                $revenuePeriod['totalVoice'] += $usage['voice_national_seconds'];

                // Calculate coverage ratio (dekningsgrad) per period,
                // then truncate crazy long floats and cast to string
                $contributionMargin = $revenuePeriod['totalRevenue'] - $revenuePeriod['totalTrafficCost'];
                if ($revenuePeriod['totalRevenue'] > 0.0) {
                    $revenuePeriod['coverageRatio'] = ($contributionMargin / $revenuePeriod['totalRevenue']) * 100;
                }

                $revenuePeriod['totalRevenue'] = bcdiv($revenuePeriod['totalRevenue'], 1, 2);
                $revenuePeriod['totalTrafficCost'] = bcdiv($revenuePeriod['totalTrafficCost'], 1, 2);
                $revenuePeriod['totalVoice'] = (string)$revenuePeriod['totalVoice'];
                $revenuePeriod['coverageRatio'] = (string)round($revenuePeriod['coverageRatio']);
            }
        }

        return $data;
    }
}
