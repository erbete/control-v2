<?php

namespace Control\Rebinding\Mappers;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RebindingRebindedAccountsMapper
{
    public static function map(LengthAwarePaginator $accounts): Collection
    {
        $page = $accounts->currentPage();
        $perPage = $accounts->perPage();
        $totalAccounts = $accounts->total();

        return $accounts->map(function ($account) use ($page, $perPage, $totalAccounts) {
            $data = json_decode($account->data);

            return [
                'page' => $page,
                'totalPages' => ceil($totalAccounts / $perPage),
                'count' => $totalAccounts,
                'perPage' => $perPage,
                'data' => [
                    'note' => $data->note ?? null,
                    'user' => $data->user ?? null,
                    'rebindedAt' => Carbon::parse($account->rebinded_at)->format('Y-m-d H:i'),
                    'accountId' => (string)$account->account_id,
                ],
            ];
        });
    }
}
