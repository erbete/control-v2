<?php

namespace Control\Rebinding\Mappers;

use Carbon\Carbon;
use Exception;

use Illuminate\Support\Collection;

class RebindingAccountDetailsMapper
{
    public static function map(Collection $account, ?string $periods): array
    {
        $parsedPeriods = self::parsePeriods($periods);

        $data = [
            'accountId' => (string)$account[0]->account_id,
            'company' => $account[0]->company,
            'contact' => $account[0]->contact,
            'phoneNumbers' => [],
        ];

        foreach ($account as $details) {
            $phoneNumber = $details->phone_number;
            $phoneData = [
                'phoneNumber' => $phoneNumber,
                'lockinEndDate' => $details->lockin_end_date,
                'usage' => [],
            ];

            foreach (json_decode($details->usages, true, JSON_UNESCAPED_UNICODE) as $usage) {
                if (count($parsedPeriods) > 0) {
                    if (!in_array($usage['period'], $parsedPeriods)) {
                        continue;
                    }
                }

                $period = $usage['period'];
                $phoneData['usage'][$period] = [
                    'priceplan' => $usage['priceplan'],
                    'subscriptionFeesRevenue' => (string)(int)$usage['subscription_fees_revenue'],
                    'gbDataUsage' => (string)round($usage['total_bytes_national'] / 1073741824, 2),
                    'totalTalkTimeMinutes' => bcdiv(($usage['voice_national_seconds'] / 60), 1, 0),
                    'contributionMargin' => bcdiv($usage['total_revenue'] - $usage['total_traffic_cost'], 1, 0),
                ];
            }

            // Append the phone data to the phoneNumbers array
            $data['phoneNumbers'][] = $phoneData;
        }

        return $data;
    }

    private static function parsePeriods(?string $periods): array
    {
        $parsedPeriods = [];
        if ($periods) {
            foreach (explode(',', $periods) as $period) {
                try {
                    $parsedPeriods[] = Carbon::createFromFormat('Ym', $period)->format('Ym');
                } catch (Exception $e) {
                    continue; // Ignore periods with a invalid format
                }
            }
        }

        return $parsedPeriods;
    }
}
