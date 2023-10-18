<?php

namespace Control\Rebinding\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Control\Infrastructure\Account;
use Control\Infrastructure\RebindingActivity;

class RebindingSetNoteAction
{
    public function execute(string $accountId, string $note): RebindingSetNoteOrStatusResult
    {
        $accountExists = true;
        $success = true;
        $conflict = false;

        DB::transaction(function () use (
            $accountId,
            $note,
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
                $activity->note = $note;
                $activity->user()->associate(Auth::user());

                // Update using optimistic concurrency control
                $rowsAffected = RebindingActivity::where('account_id', $accountId)
                    ->where('updated_at', $lastUpdate)
                    ->update([
                        'note' => $note,
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
                $activity->note = $note;
                $activity->user()->associate(Auth::user());
                $activity->account()->associate(Account::find($accountId));

                if (!$activity->save()) {
                    $success = false;
                    DB::rollBack();
                    return;
                }
            }
        }, 5);

        return new RebindingSetNoteOrStatusResult(
            accountExists: $accountExists,
            success: $success,
            conflict: $conflict,
        );
    }
}
