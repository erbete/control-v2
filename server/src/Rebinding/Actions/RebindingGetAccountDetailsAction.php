<?php

namespace Control\Rebinding\Actions;

use Illuminate\Support\Facades\DB;

class RebindingGetAccountDetailsAction
{
    public function execute(string $accountId)
    {
        return DB::table('rebinding_accounts', 'ra')
            ->where('ra.account_id', $accountId)
            ->leftJoin('rebinding_account_usages AS rau', 'rau.account_id', '=', 'ra.account_id')
            ->get([
                'ra.account_id',
                'ra.contact',
                'ra.company',
                'rau.phone_number',
                'rau.lockin_end_date',
                'rau.usages',
            ]);
    }
}
