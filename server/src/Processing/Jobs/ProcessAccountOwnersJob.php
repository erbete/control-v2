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

use Control\Infrastructure\AccountOwner;
use Control\Infrastructure\Address;
use Control\Infrastructure\Email;

class ProcessAccountOwnersJob implements ShouldQueue, ShouldBeUnique
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
                DB::table('account_owners')->delete(); // cannot use ->truncate inside a transaction
                DB::table('emails')->delete(); // cannot use ->truncate inside a transaction
                DB::table('addresses')->delete(); // cannot use ->truncate inside a transaction
                DB::statement('ALTER SEQUENCE emails_id_seq RESTART WITH 1'); // reset pkey back to 1
                DB::statement('ALTER SEQUENCE addresses_id_seq RESTART WITH 1'); // reset pkey back to 1

                DB::table('erate_accounts', 'ea')
                    ->select([
                        'ea.owner_id',
                        'eu.first_name',
                        'eu.last_name',
                        'eu.company',
                        'eu.personal_id',
                        'eu.street',
                        'eu.address2',
                        'eu.city',
                        'eu.zip',
                        'eu.country',
                        DB::raw('COALESCE(eu.email, eu.email2) AS email'),
                        'eu.birthday'
                    ])
                    ->leftJoin('erate_users AS eu', 'eu.id', '=', 'ea.owner_id')
                    ->orderBy('ea.id')
                    ->chunk(2000, function ($chunks) {
                        $accountOwnersBatch = [];
                        $addressesBatch = [];
                        $emailsBatch = [];
                        foreach ($chunks as $chunk) {
                            $accountOwnersBatch[] = [
                                'id' => $chunk->owner_id,
                                'first_name' => $chunk->first_name,
                                'last_name' => $chunk->last_name,
                                'company' => $chunk->company,
                                'personal_id' => $chunk->personal_id,
                            ];

                            $address = trim($chunk->street . ' ' . $chunk->address2);
                            $addressesBatch[] = [
                                'address' => empty($address) ? null : $address,
                                'city' => $chunk->city,
                                'zip' => $chunk->zip,
                                'country' => $chunk->country,
                                'account_owner_id' => $chunk->owner_id,
                            ];

                            if (isset($chunk->email)) {
                                $emailsBatch[] = [
                                    'address' => $chunk->email,
                                    'account_owner_id' => $chunk->owner_id,
                                ];
                            }
                        }

                        // Save data to the database
                        AccountOwner::insert($accountOwnersBatch);
                        Address::insert($addressesBatch);
                        Email::insert($emailsBatch);
                    });
            });

            Log::channel('stack')->info('ProcessAccountOwnersJob: done');
        } catch (\Throwable $e) {
            Log::channel('stack')->error('ProcessAccountOwnersJob: ' . $e->getMessage());
            throw $e;
        }
    }
}
