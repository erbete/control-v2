<?php

namespace Control\Rebinding\Actions;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Control\Infrastructure\Account;
use Control\Infrastructure\RebindingActivity;
use Control\Infrastructure\RebindingAccount;
use Control\Infrastructure\User;
use Control\Rebinding\RebindingStatus;

class RebindingSetStatusAction
{
    public function execute(string $accountId, string $status): RebindingSetNoteOrStatusResult
    {
        $accountExists = true;
        $success = true;
        $conflict = false;

        DB::transaction(function () use (
            $accountId,
            $status,
            &$accountExists,
            &$success,
            &$conflict,
        ) {
            // Check if account exists before continue
            if (!DB::table('rebinding_accounts')->where('account_id', $accountId)->exists()) {
                $accountExists = false;
                return;
            }

            // Check if an entry with the given account ID already exists
            $activity = RebindingActivity::firstWhere('account_id', $accountId);

            if ($activity) {
                $lastUpdate = $activity->updated_at;
                $activity->status = $status;
                $activity->user()->associate(Auth::user());

                // Update using optimistic concurrency control
                $rowsAffected = RebindingActivity::where('account_id', $accountId)
                    ->where('updated_at', $lastUpdate)
                    ->update([
                        'status' => $status,
                        'user_id' => Auth::id()
                    ]);

                if ($rowsAffected === 0) {
                    $conflict = true;
                    DB::rollBack();
                    return;
                }
            } else {
                // If it doesn't exist, create a new one
                $activity = new RebindingActivity();
                $activity->status = $status;
                $activity->user()->associate(Auth::user());
                $activity->account()->associate(Account::find($accountId));

                if (!$activity->save()) {
                    $success = false;
                    DB::rollBack();
                    return;
                }
            }

            // Create snapshot for the rebinded customer.
            if ($status === RebindingStatus::REBINDED->value) {
                $success = $this->rebindAccount($activity, $accountId);
                if (!$success) DB::rollBack();
                return;
            }
        }, 5);

        return new RebindingSetNoteOrStatusResult(
            accountExists: $accountExists,
            success: $success,
            conflict: $conflict,
        );
    }

    // A customer with the REBINDED status is temporary; it resets to NONE after a few days.
    // This reset occurs because the 'lockin_end_date' for a rebinded customer is updated with each new batch.
    // Once updated, the customer becomes invisible until the next 7-month window.
    // Hence, their status must revert to NONE instead of remaining as REBINDED when that 7-month window is in range.
    // See RebindingCollectCustomerDataJob for how the rebinded customers statuses are reset.
    private function rebindAccount(RebindingActivity $activity, string $accountId): bool
    {
        $data = [
            'note' => $activity->note,
            'user' => User::where('id', Auth::id())->value('name'),
        ];

        $affectedRows = RebindingAccount::query()
            ->insert([
                'data' => json_encode($data),
                'account_id' => $accountId,
            ]);

        if ($affectedRows === 0) {
            return false;
        }

        $this->updateCachedAccountCount();

        return true;
    }

    // Updates cached total accounts count when a customer is rebinded
    private function updateCachedAccountCount()
    {
        $redis = Redis::connection();
        $redis->select((int)env('REDIS_CACHE_DB')); // cache db = 1

        $pattern = 'control_cache:rebindingTotalAccounts:*';
        $cursor = 0;
        $keys = [];

        do {
            list($cursor, $results) = $redis->scan($cursor, 'MATCH', $pattern, 'COUNT', 1000);
            $keys = array_merge($keys, $results);
        } while ($cursor);

        foreach ($keys as $key) {
            $currentCount = (int)$redis->get($key);
            $newValue = $currentCount - 1;
            if ($newValue < 0) continue;
            $redis->set($key, $newValue);
        }
    }
}
