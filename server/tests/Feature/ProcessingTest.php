<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\DatabaseMigrations;

use Control\Processing\Jobs\ProcessErateAccountsJob;
use Control\Processing\Jobs\ProcessErateSubscriptionsJob;
use Control\Processing\Jobs\ProcessErateUsersJob;
use Control\Processing\Jobs\ProcessErateNPExportJob;
use Control\Processing\Jobs\ProcessErateNPImportJob;
use Control\Processing\Jobs\ProcessAccountOwnersJob;
use Control\Processing\Jobs\ProcessAccountsJob;
use Control\Processing\Jobs\ProcessSubscriptionsJob;

class ProcessingTest extends TestCase
{
    use DatabaseMigrations;

    public function test_dispatch_process_jobs()
    {
        // Arrange
        Bus::fake();

        // Act
        ProcessErateAccountsJob::dispatch();
        ProcessErateUsersJob::dispatch();
        ProcessErateSubscriptionsJob::dispatch();
        ProcessErateNPExportJob::dispatch();
        ProcessErateNPImportJob::dispatch();
        ProcessAccountOwnersJob::dispatch();
        ProcessAccountsJob::dispatch();
        ProcessSubscriptionsJob::dispatch();

        // Assert
        Bus::assertDispatchedTimes(ProcessErateAccountsJob::class);
        Bus::assertDispatchedTimes(ProcessErateUsersJob::class);
        Bus::assertDispatchedTimes(ProcessErateSubscriptionsJob::class);
        Bus::assertDispatchedTimes(ProcessErateNPExportJob::class);
        Bus::assertDispatchedTimes(ProcessErateNPImportJob::class);
        Bus::assertDispatchedTimes(ProcessAccountOwnersJob::class);
        Bus::assertDispatchedTimes(ProcessAccountsJob::class);
        Bus::assertDispatchedTimes(ProcessSubscriptionsJob::class);
    }

    public function test_process_jobs_pushed_to_queue()
    {
        // Arrange
        Queue::fake();
        Queue::assertNothingPushed();

        // Act
        $this->artisan('run:process');

        // Assert
        Queue::assertPushedOn('processing', ProcessErateAccountsJob::class);
        Queue::assertPushed(ProcessErateAccountsJob::class, 1);

        Queue::assertPushedOn('processing', ProcessErateUsersJob::class);
        Queue::assertPushed(ProcessErateUsersJob::class, 1);

        Queue::assertPushedOn('processing', ProcessErateSubscriptionsJob::class);
        Queue::assertPushed(ProcessErateSubscriptionsJob::class, 1);

        Queue::assertPushedOn('processing', ProcessErateNPExportJob::class);
        Queue::assertPushed(ProcessErateNPExportJob::class, 1);

        Queue::assertPushedOn('processing', ProcessErateNPImportJob::class);
        Queue::assertPushed(ProcessErateNPImportJob::class, 1);

        // can only assert the first job in a chain for whatever reason...
        Queue::assertPushedOn('processing', ProcessAccountOwnersJob::class);
        Queue::assertPushed(ProcessAccountOwnersJob::class, 1);

        // Queue::assertPushedOn('dump-processing', ProcessAccountsJob::class);
        // Queue::assertPushed(ProcessAccountsJob::class, 1);

        // Queue::assertPushedOn('dump-processing', ProcessSubscriptionsJob::class);
        // Queue::assertPushed(ProcessSubscriptionsJob::class, 1);
    }

    public function test_processing_and_mapping()
    {
        $this->artisan('run:process')->assertSuccessful();

        $this->assertDatabaseCount('erate_accounts', 3);
        $this->assertDatabaseCount('erate_users', 30);
        $this->assertDatabaseCount('erate_subscriptions', 28);

        $this->assertDatabaseCount('account_owners', 3);
        $this->assertDatabaseCount('accounts', 3);
        $this->assertDatabaseCount('emails', 3);
        $this->assertDatabaseCount('addresses', 3);
        $this->assertDatabaseCount('subscriptions', 28);

        //     ///
        //     /// #1 account owner
        //     ///
        //     $owner = CDRatorAccountOwner::find(202203161008083891);
        //     $this->assertDatabaseHas('cdrator_account_owners', ['id' => 202203161008083891]);
        //     $this->assertEquals('Espen', $owner->first_name);
        //     $this->assertEquals('Ødegaard', $owner->last_name);
        //     $this->assertEquals(null, $owner->date_of_birth);
        //     $this->assertEquals('Stortransport AS', $owner->company);
        //     $this->assertEquals('927106421', $owner->uid);
        //     $this->assertEquals('123_username', $owner->username);

        //     // addresses
        //     $addresses = $owner->addresses()->get();
        //     $this->assertEquals(1, $addresses->count());
        //     $this->assertEquals('Kragerø Næringspark Krokenveien 133', $addresses[0]->address);
        //     $this->assertEquals('Sannidal', $addresses[0]->city);
        //     $this->assertEquals('3766', $addresses[0]->zip);
        //     $this->assertEquals(null, $addresses[0]->country);

        //     // emails
        //     $emails = $owner->emails()->get();
        //     $this->assertEquals($emails->count(), 1);
        //     $this->assertEquals('espen.ødegaard@stortransport.no', $emails[0]->address);

        //     // accounts
        //     $accounts = $owner->accounts()->get();
        //     $this->assertEquals('1', $owner->accounts->count());
        //     $this->assertDatabaseHas('cdrator_accounts', ['id' => 202203161008083901]);
        //     $this->assertEquals('1', $accounts[0]->account_type);
        //     $this->assertEquals('8008888', $accounts[0]->customer_number);
        //     $this->assertEquals($accounts[0]->cdrator_account_owner_id, $owner->id);

        //     // subscriptions (for account #202203161008083901)
        //     $subs = $accounts[0]->subscriptions()->get();
        //     $this->assertEquals(26, $subs->count());
        //     $this->assertEquals(202203161008156031, $subs[0]->id);
        //     $this->assertEquals(202203161008083901, $subs[0]->cdrator_account_id);
        //     $this->assertEquals('96424821', $subs[0]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[0]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[0]->start_date);
        //     $this->assertEquals(null, $subs[0]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[0]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[0]->lockin_end_date);
        //     $this->assertEquals('24', $subs[0]->lockin_length);
        //     $this->assertEquals('2', $subs[0]->status_id);
        //     $this->assertEquals('200', $subs[0]->service_status);
        //     $this->assertEquals('245345453581549', $subs[0]->imsi);
        //     $this->assertEquals('nortelweb', $subs[0]->entered_by);
        //     $this->assertEquals('Per Yngvar', $subs[0]->first_name);
        //     $this->assertEquals('Eikeskog', $subs[0]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156032, $subs[1]->id);
        //     $this->assertEquals(202203161008083901, $subs[1]->cdrator_account_id);
        //     $this->assertEquals('96254831', $subs[1]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[1]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[1]->start_date);
        //     $this->assertEquals(null, $subs[1]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[1]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[1]->lockin_end_date);
        //     $this->assertEquals('24', $subs[1]->lockin_length);
        //     $this->assertEquals('2', $subs[1]->status_id);
        //     $this->assertEquals('200', $subs[1]->service_status);
        //     $this->assertEquals('2420145t5643549', $subs[1]->imsi);
        //     $this->assertEquals('nortelweb', $subs[1]->entered_by);
        //     $this->assertEquals('Anna', $subs[1]->first_name);
        //     $this->assertEquals('Johansen Vårås', $subs[1]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156033, $subs[2]->id);
        //     $this->assertEquals(202203161008083901, $subs[2]->cdrator_account_id);
        //     $this->assertEquals('94454321', $subs[2]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[2]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[2]->start_date);
        //     $this->assertEquals(null, $subs[2]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[2]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[2]->lockin_end_date);
        //     $this->assertEquals('24', $subs[2]->lockin_length);
        //     $this->assertEquals('2', $subs[2]->status_id);
        //     $this->assertEquals('200', $subs[2]->service_status);
        //     $this->assertEquals('454575763581549', $subs[2]->imsi);
        //     $this->assertEquals('nortelweb', $subs[2]->entered_by);
        //     $this->assertEquals('Alf', $subs[2]->first_name);
        //     $this->assertEquals('Vårås', $subs[2]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156034, $subs[3]->id);
        //     $this->assertEquals(202203161008083901, $subs[3]->cdrator_account_id);
        //     $this->assertEquals('44454123', $subs[3]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[3]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[3]->start_date);
        //     $this->assertEquals(null, $subs[3]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[3]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[3]->lockin_end_date);
        //     $this->assertEquals('24', $subs[3]->lockin_length);
        //     $this->assertEquals('2', $subs[3]->status_id);
        //     $this->assertEquals('200', $subs[3]->service_status);
        //     $this->assertEquals('242014584684649', $subs[3]->imsi);
        //     $this->assertEquals('nortelweb', $subs[3]->entered_by);
        //     $this->assertEquals('Olav', $subs[3]->first_name);
        //     $this->assertEquals('Haug Brendtøy', $subs[3]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156035, $subs[4]->id);
        //     $this->assertEquals(202203161008083901, $subs[4]->cdrator_account_id);
        //     $this->assertEquals('99451133', $subs[4]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[4]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[4]->start_date);
        //     $this->assertEquals(null, $subs[4]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[4]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[4]->lockin_end_date);
        //     $this->assertEquals('24', $subs[4]->lockin_length);
        //     $this->assertEquals('2', $subs[4]->status_id);
        //     $this->assertEquals('200', $subs[4]->service_status);
        //     $this->assertEquals('246454513581549', $subs[4]->imsi);
        //     $this->assertEquals('nortelweb', $subs[4]->entered_by);
        //     $this->assertEquals('Christian', $subs[4]->first_name);
        //     $this->assertEquals('Kittilson', $subs[4]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156036, $subs[5]->id);
        //     $this->assertEquals(202203161008083901, $subs[5]->cdrator_account_id);
        //     $this->assertEquals('91555193', $subs[5]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[5]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[5]->start_date);
        //     $this->assertEquals(null, $subs[5]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[5]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[5]->lockin_end_date);
        //     $this->assertEquals('24', $subs[5]->lockin_length);
        //     $this->assertEquals('2', $subs[5]->status_id);
        //     $this->assertEquals('200', $subs[5]->service_status);
        //     $this->assertEquals('242035609581549', $subs[5]->imsi);
        //     $this->assertEquals('nortelweb', $subs[5]->entered_by);
        //     $this->assertEquals('Sindre', $subs[5]->first_name);
        //     $this->assertEquals('Godag Ludvig', $subs[5]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156037, $subs[6]->id);
        //     $this->assertEquals(202203161008083901, $subs[6]->cdrator_account_id);
        //     $this->assertEquals('95297690', $subs[6]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[6]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[6]->start_date);
        //     $this->assertEquals(null, $subs[6]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[6]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[6]->lockin_end_date);
        //     $this->assertEquals('24', $subs[6]->lockin_length);
        //     $this->assertEquals('2', $subs[6]->status_id);
        //     $this->assertEquals('200', $subs[6]->service_status);
        //     $this->assertEquals('242014514560949', $subs[6]->imsi);
        //     $this->assertEquals('nortelweb', $subs[6]->entered_by);
        //     $this->assertEquals('Åge', $subs[6]->first_name);
        //     $this->assertEquals('Solstad', $subs[6]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156038, $subs[7]->id);
        //     $this->assertEquals(202203161008083901, $subs[7]->cdrator_account_id);
        //     $this->assertEquals('98495023', $subs[7]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[7]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[7]->start_date);
        //     $this->assertEquals(null, $subs[7]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[7]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[7]->lockin_end_date);
        //     $this->assertEquals('24', $subs[7]->lockin_length);
        //     $this->assertEquals('2', $subs[7]->status_id);
        //     $this->assertEquals('200', $subs[7]->service_status);
        //     $this->assertEquals('234509513581549', $subs[7]->imsi);
        //     $this->assertEquals('nortelweb', $subs[7]->entered_by);
        //     $this->assertEquals('Alex', $subs[7]->first_name);
        //     $this->assertEquals('Larsen', $subs[7]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156039, $subs[8]->id);
        //     $this->assertEquals(202203161008083901, $subs[8]->cdrator_account_id);
        //     $this->assertEquals('98842963', $subs[8]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[8]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[8]->start_date);
        //     $this->assertEquals(null, $subs[8]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[8]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[8]->lockin_end_date);
        //     $this->assertEquals('24', $subs[8]->lockin_length);
        //     $this->assertEquals('2', $subs[8]->status_id);
        //     $this->assertEquals('200', $subs[8]->service_status);
        //     $this->assertEquals('242013245081549', $subs[8]->imsi);
        //     $this->assertEquals('nortelweb', $subs[8]->entered_by);
        //     $this->assertEquals('Leif Tore', $subs[8]->first_name);
        //     $this->assertEquals('Eide', $subs[8]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156010, $subs[9]->id);
        //     $this->assertEquals(202203161008083901, $subs[9]->cdrator_account_id);
        //     $this->assertEquals('96448292', $subs[9]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[9]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[9]->start_date);
        //     $this->assertEquals(null, $subs[9]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[9]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[9]->lockin_end_date);
        //     $this->assertEquals('24', $subs[9]->lockin_length);
        //     $this->assertEquals('2', $subs[9]->status_id);
        //     $this->assertEquals('200', $subs[9]->service_status);
        //     $this->assertEquals('242014513091549', $subs[9]->imsi);
        //     $this->assertEquals('nortelweb', $subs[9]->entered_by);
        //     $this->assertEquals('Odd', $subs[9]->first_name);
        //     $this->assertEquals('Leifsson', $subs[9]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156011, $subs[10]->id);
        //     $this->assertEquals(202203161008083901, $subs[10]->cdrator_account_id);
        //     $this->assertEquals('98833922', $subs[10]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[10]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[10]->start_date);
        //     $this->assertEquals(null, $subs[10]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[10]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[10]->lockin_end_date);
        //     $this->assertEquals('24', $subs[10]->lockin_length);
        //     $this->assertEquals('2', $subs[10]->status_id);
        //     $this->assertEquals('200', $subs[10]->service_status);
        //     $this->assertEquals('234514513581549', $subs[10]->imsi);
        //     $this->assertEquals('nortelweb', $subs[10]->entered_by);
        //     $this->assertEquals('Vigdis', $subs[10]->first_name);
        //     $this->assertEquals('Øveraas', $subs[10]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156012, $subs[11]->id);
        //     $this->assertEquals(202203161008083901, $subs[11]->cdrator_account_id);
        //     $this->assertEquals('98884932', $subs[11]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[11]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[11]->start_date);
        //     $this->assertEquals(null, $subs[11]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[11]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[11]->lockin_end_date);
        //     $this->assertEquals('24', $subs[11]->lockin_length);
        //     $this->assertEquals('2', $subs[11]->status_id);
        //     $this->assertEquals('200', $subs[11]->service_status);
        //     $this->assertEquals('242453013581549', $subs[11]->imsi);
        //     $this->assertEquals('nortelweb', $subs[11]->entered_by);
        //     $this->assertEquals('Roger', $subs[11]->first_name);
        //     $this->assertEquals('Haugen', $subs[11]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156013, $subs[12]->id);
        //     $this->assertEquals(202203161008083901, $subs[12]->cdrator_account_id);
        //     $this->assertEquals('91136342', $subs[12]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[12]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[12]->start_date);
        //     $this->assertEquals(null, $subs[12]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[12]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[12]->lockin_end_date);
        //     $this->assertEquals('24', $subs[12]->lockin_length);
        //     $this->assertEquals('2', $subs[12]->status_id);
        //     $this->assertEquals('200', $subs[12]->service_status);
        //     $this->assertEquals('242014332181549', $subs[12]->imsi);
        //     $this->assertEquals('nortelweb', $subs[12]->entered_by);
        //     $this->assertEquals('Tom', $subs[12]->first_name);
        //     $this->assertEquals('Klausen', $subs[12]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156014, $subs[13]->id);
        //     $this->assertEquals(202203161008083901, $subs[13]->cdrator_account_id);
        //     $this->assertEquals('93442562', $subs[13]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[13]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[13]->start_date);
        //     $this->assertEquals(null, $subs[13]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[13]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[13]->lockin_end_date);
        //     $this->assertEquals('24', $subs[13]->lockin_length);
        //     $this->assertEquals('2', $subs[13]->status_id);
        //     $this->assertEquals('200', $subs[13]->service_status);
        //     $this->assertEquals('242014513569949', $subs[13]->imsi);
        //     $this->assertEquals('nortelweb', $subs[13]->entered_by);
        //     $this->assertEquals('Espen', $subs[13]->first_name);
        //     $this->assertEquals('Ødegaard', $subs[13]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156015, $subs[14]->id);
        //     $this->assertEquals(202203161008083901, $subs[14]->cdrator_account_id);
        //     $this->assertEquals('98857302', $subs[14]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[14]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[14]->start_date);
        //     $this->assertEquals(null, $subs[14]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[14]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[14]->lockin_end_date);
        //     $this->assertEquals('24', $subs[14]->lockin_length);
        //     $this->assertEquals('2', $subs[14]->status_id);
        //     $this->assertEquals('200', $subs[14]->service_status);
        //     $this->assertEquals('509014513581549', $subs[14]->imsi);
        //     $this->assertEquals('nortelweb', $subs[14]->entered_by);
        //     $this->assertEquals('Pål', $subs[14]->first_name);
        //     $this->assertEquals('Tyvand Jordheim', $subs[14]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156016, $subs[15]->id);
        //     $this->assertEquals(202203161008083901, $subs[15]->cdrator_account_id);
        //     $this->assertEquals('93434622', $subs[15]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[15]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[15]->start_date);
        //     $this->assertEquals(null, $subs[15]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[15]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[15]->lockin_end_date);
        //     $this->assertEquals('24', $subs[15]->lockin_length);
        //     $this->assertEquals('2', $subs[15]->status_id);
        //     $this->assertEquals('200', $subs[15]->service_status);
        //     $this->assertEquals('242324513581549', $subs[15]->imsi);
        //     $this->assertEquals('nortelweb', $subs[15]->entered_by);
        //     $this->assertEquals('Frank', $subs[15]->first_name);
        //     $this->assertEquals('Jørgenson', $subs[15]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156017, $subs[16]->id);
        //     $this->assertEquals(202203161008083901, $subs[16]->cdrator_account_id);
        //     $this->assertEquals('96565322', $subs[16]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[16]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[16]->start_date);
        //     $this->assertEquals(null, $subs[16]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[16]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[16]->lockin_end_date);
        //     $this->assertEquals('24', $subs[16]->lockin_length);
        //     $this->assertEquals('2', $subs[16]->status_id);
        //     $this->assertEquals('200', $subs[16]->service_status);
        //     $this->assertEquals('242019093581549', $subs[16]->imsi);
        //     $this->assertEquals('nortelweb', $subs[16]->entered_by);
        //     $this->assertEquals('Jan', $subs[16]->first_name);
        //     $this->assertEquals('Stein Øygard', $subs[16]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156018, $subs[17]->id);
        //     $this->assertEquals(202203161008083901, $subs[17]->cdrator_account_id);
        //     $this->assertEquals('98884372', $subs[17]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[17]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[17]->start_date);
        //     $this->assertEquals(null, $subs[17]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[17]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[17]->lockin_end_date);
        //     $this->assertEquals('24', $subs[17]->lockin_length);
        //     $this->assertEquals('2', $subs[17]->status_id);
        //     $this->assertEquals('200', $subs[17]->service_status);
        //     $this->assertEquals('242014500990849', $subs[17]->imsi);
        //     $this->assertEquals('nortelweb', $subs[17]->entered_by);
        //     $this->assertEquals('Linn', $subs[17]->first_name);
        //     $this->assertEquals('Farstad', $subs[17]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156019, $subs[18]->id);
        //     $this->assertEquals(202203161008083901, $subs[18]->cdrator_account_id);
        //     $this->assertEquals('95975291', $subs[18]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[18]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[18]->start_date);
        //     $this->assertEquals(null, $subs[18]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[18]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[18]->lockin_end_date);
        //     $this->assertEquals('24', $subs[18]->lockin_length);
        //     $this->assertEquals('2', $subs[18]->status_id);
        //     $this->assertEquals('200', $subs[18]->service_status);
        //     $this->assertEquals('564090813581549', $subs[18]->imsi);
        //     $this->assertEquals('nortelweb', $subs[18]->entered_by);
        //     $this->assertEquals('Espen', $subs[18]->first_name);
        //     $this->assertEquals('Ødegaard', $subs[18]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156020, $subs[19]->id);
        //     $this->assertEquals(202203161008083901, $subs[19]->cdrator_account_id);
        //     $this->assertEquals('95838732', $subs[19]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[19]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[19]->start_date);
        //     $this->assertEquals(null, $subs[19]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[19]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[19]->lockin_end_date);
        //     $this->assertEquals('24', $subs[19]->lockin_length);
        //     $this->assertEquals('2', $subs[19]->status_id);
        //     $this->assertEquals('200', $subs[19]->service_status);
        //     $this->assertEquals('242014898281549', $subs[19]->imsi);
        //     $this->assertEquals('nortelweb', $subs[19]->entered_by);
        //     $this->assertEquals('Haakon', $subs[19]->first_name);
        //     $this->assertEquals('Knutsen', $subs[19]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156021, $subs[20]->id);
        //     $this->assertEquals(202203161008083901, $subs[20]->cdrator_account_id);
        //     $this->assertEquals('42309532', $subs[20]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[20]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[20]->start_date);
        //     $this->assertEquals(null, $subs[20]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[20]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[20]->lockin_end_date);
        //     $this->assertEquals('24', $subs[20]->lockin_length);
        //     $this->assertEquals('2', $subs[20]->status_id);
        //     $this->assertEquals('200', $subs[20]->service_status);
        //     $this->assertEquals('242014513989249', $subs[20]->imsi);
        //     $this->assertEquals('nortelweb', $subs[20]->entered_by);
        //     $this->assertEquals('Magnus', $subs[20]->first_name);
        //     $this->assertEquals('Snøås', $subs[20]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156022, $subs[21]->id);
        //     $this->assertEquals(202203161008083901, $subs[21]->cdrator_account_id);
        //     $this->assertEquals('42112322', $subs[21]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[21]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[21]->start_date);
        //     $this->assertEquals(null, $subs[21]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[21]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[21]->lockin_end_date);
        //     $this->assertEquals('24', $subs[21]->lockin_length);
        //     $this->assertEquals('2', $subs[21]->status_id);
        //     $this->assertEquals('200', $subs[21]->service_status);
        //     $this->assertEquals('242014513581449', $subs[21]->imsi);
        //     $this->assertEquals('nortelweb', $subs[21]->entered_by);
        //     $this->assertEquals('Steinar', $subs[21]->first_name);
        //     $this->assertEquals('Nes', $subs[21]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156023, $subs[22]->id);
        //     $this->assertEquals(202203161008083901, $subs[22]->cdrator_account_id);
        //     $this->assertEquals('42344352', $subs[22]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[22]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[22]->start_date);
        //     $this->assertEquals(null, $subs[22]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[22]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[22]->lockin_end_date);
        //     $this->assertEquals('24', $subs[22]->lockin_length);
        //     $this->assertEquals('2', $subs[22]->status_id);
        //     $this->assertEquals('200', $subs[22]->service_status);
        //     $this->assertEquals('242012342451549', $subs[22]->imsi);
        //     $this->assertEquals('nortelweb', $subs[22]->entered_by);
        //     $this->assertEquals('Finn', $subs[22]->first_name);
        //     $this->assertEquals('Dalene', $subs[22]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156025, $subs[23]->id);
        //     $this->assertEquals(202203161008083901, $subs[23]->cdrator_account_id);
        //     $this->assertEquals('42345352', $subs[23]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[23]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[23]->start_date);
        //     $this->assertEquals(null, $subs[23]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[23]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[23]->lockin_end_date);
        //     $this->assertEquals('24', $subs[23]->lockin_length);
        //     $this->assertEquals('2', $subs[23]->status_id);
        //     $this->assertEquals('200', $subs[23]->service_status);
        //     $this->assertEquals('545552513581549', $subs[23]->imsi);
        //     $this->assertEquals('nortelweb', $subs[23]->entered_by);
        //     $this->assertEquals('Ken', $subs[23]->first_name);
        //     $this->assertEquals('Breiskog', $subs[23]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156026, $subs[24]->id);
        //     $this->assertEquals(202203161008083901, $subs[24]->cdrator_account_id);
        //     $this->assertEquals('92345123', $subs[24]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[24]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[24]->start_date);
        //     $this->assertEquals(null, $subs[24]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[24]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[24]->lockin_end_date);
        //     $this->assertEquals('24', $subs[24]->lockin_length);
        //     $this->assertEquals('2', $subs[24]->status_id);
        //     $this->assertEquals('200', $subs[24]->service_status);
        //     $this->assertEquals('242012434581549', $subs[24]->imsi);
        //     $this->assertEquals('nortelweb', $subs[24]->entered_by);
        //     $this->assertEquals('Ann', $subs[24]->first_name);
        //     $this->assertEquals('Hovdegaard', $subs[24]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     $this->assertEquals(202203161008156027, $subs[25]->id);
        //     $this->assertEquals(202203161008083901, $subs[25]->cdrator_account_id);
        //     $this->assertEquals('91234563', $subs[25]->phone_number);
        //     $this->assertEquals('2022-03-16', $subs[25]->establish_date);
        //     $this->assertEquals('2022-03-30', $subs[25]->start_date);
        //     $this->assertEquals(null, $subs[25]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[25]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[25]->lockin_end_date);
        //     $this->assertEquals('24', $subs[25]->lockin_length);
        //     $this->assertEquals('2', $subs[25]->status_id);
        //     $this->assertEquals('200', $subs[25]->service_status);
        //     $this->assertEquals('242014513445649', $subs[25]->imsi);
        //     $this->assertEquals('nortelweb', $subs[25]->entered_by);
        //     $this->assertEquals('Mats', $subs[25]->first_name);
        //     $this->assertEquals('Fossen', $subs[25]->last_name);
        //     foreach ($subs as $sub) {
        //         $this->assertNotEquals($owner->id, $sub->owner_id);
        //     }

        //     // subscription bundles (for subscription #202203161008156031)
        //     $bundles = $subs[0]->bundles()->get();
        //     $this->assertEquals(2, $bundles->count());
        //     $this->assertEquals(202203161008156031, $subs[0]->id);
        //     $this->assertEquals(20220123, $bundles[0]->period);
        //     $this->assertEquals(10485760000, $bundles[0]->value_1);
        //     $this->assertEquals(1637826000, $bundles[0]->value_2);
        //     $this->assertEquals('DATA-SPLIT', $bundles[0]->code);
        //     $this->assertEquals('Nortel Grenseløs', $bundles[0]->name);

        //     $this->assertEquals(202203161008156031, $subs[0]->id);
        //     $this->assertEquals(20220321, $bundles[1]->period);
        //     $this->assertEquals(10485760001, $bundles[1]->value_1);
        //     $this->assertEquals(1637826001, $bundles[1]->value_2);
        //     $this->assertEquals('DATA-SPLIT', $bundles[1]->code);
        //     $this->assertEquals('Nortel Grenseløs', $bundles[1]->name);

        //     // service options (for subscription #202203161008156031)
        //     $serviceOptions = $subs[0]->serviceOptions();
        //     $this->assertEquals(2, $serviceOptions->count());
        //     $this->assertEquals($subs[0]->id, $serviceOptions->first()->cdrator_subscription_id);
        //     $this->assertEquals('2019-11-12', $serviceOptions->first()->start_date);
        //     $this->assertEquals(201805240844083553, $serviceOptions->first()->cdrator_product_option_id);

        //     $this->assertEquals($subs[0]->id, $serviceOptions->skip(1)->first()->cdrator_subscription_id);
        //     $this->assertEquals('2019-11-13', $serviceOptions->skip(1)->first()->start_date);
        //     $this->assertEquals(201908161125064674, $serviceOptions->skip(1)->first()->cdrator_product_option_id);

        //     //  customer revenue figures (for subscription #202203161008156031)
        //     $revenue = $subs[0]->revenue()->get();
        //     $this->assertEquals(202203161008156031, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[1]->revenue()->get();
        //     $this->assertEquals(202203161008156032, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[2]->revenue()->get();
        //     $this->assertEquals(202203161008156033, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[3]->revenue()->get();
        //     $this->assertEquals(202203161008156034, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[4]->revenue()->get();
        //     $this->assertEquals(202203161008156035, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[5]->revenue()->get();
        //     $this->assertEquals(202203161008156036, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[6]->revenue()->get();
        //     $this->assertEquals(202203161008156037, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[7]->revenue()->get();
        //     $this->assertEquals(202203161008156038, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[8]->revenue()->get();
        //     $this->assertEquals(202203161008156039, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[9]->revenue()->get();
        //     $this->assertEquals(202203161008156010, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[10]->revenue()->get();
        //     $this->assertEquals(202203161008156011, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[11]->revenue()->get();
        //     $this->assertEquals(202203161008156012, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[12]->revenue()->get();
        //     $this->assertEquals(202203161008156013, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[13]->revenue()->get();
        //     $this->assertEquals(202203161008156014, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[14]->revenue()->get();
        //     $this->assertEquals(202203161008156015, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[15]->revenue()->get();
        //     $this->assertEquals(202203161008156016, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[16]->revenue()->get();
        //     $this->assertEquals(202203161008156017, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[17]->revenue()->get();
        //     $this->assertEquals(202203161008156018, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[18]->revenue()->get();
        //     $this->assertEquals(202203161008156019, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[19]->revenue()->get();
        //     $this->assertEquals(202203161008156020, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[20]->revenue()->get();
        //     $this->assertEquals(202203161008156021, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[21]->revenue()->get();
        //     $this->assertEquals(202203161008156022, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[22]->revenue()->get();
        //     $this->assertEquals(202203161008156023, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[23]->revenue()->get();
        //     $this->assertEquals(202203161008156025, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[24]->revenue()->get();
        //     $this->assertEquals(202203161008156026, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.0000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.00, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     $revenue = $subs[25]->revenue()->get();
        //     $this->assertEquals(202203161008156027, $revenue[0]->cdrator_subscription_id);
        //     $this->assertEquals(202200, $revenue[0]->period);
        //     $this->assertEquals(100.1000, $revenue[0]->total_revenue);
        //     $this->assertEquals(50.10, $revenue[0]->total_traffic_cost);
        //     $this->assertEquals(1234, $revenue[0]->voice_national_seconds);

        //     ///
        //     /// #2 account owner
        //     ///
        //     $owner = CDRatorAccountOwner::find(202006031246537166);
        //     $this->assertDatabaseHas('cdrator_account_owners', ['id' => 202006031246537166]);
        //     $this->assertEquals('Toralf', $owner->first_name);
        //     $this->assertEquals('Gamling', $owner->last_name);
        //     $this->assertEquals('1964-04-12', $owner->date_of_birth);
        //     $this->assertEquals(null, $owner->company);
        //     $this->assertEquals('12046411101', $owner->uid);

        //     // addresses
        //     $addresses = $owner->addresses()->get();
        //     $this->assertEquals($addresses->count(), 1);
        //     $this->assertEquals('Solheimsveien 11', $addresses[0]->address);
        //     $this->assertEquals('Søvik', $addresses[0]->city);
        //     $this->assertEquals('6280', $addresses[0]->zip);
        //     $this->assertEquals(null, $addresses[0]->country);

        //     // emails
        //     $emails = $owner->emails()->get();
        //     $this->assertEquals(1, $emails->count());
        //     $this->assertEquals('toralf@gamling.no', $emails[0]->address);

        //     // accounts
        //     $accounts = $owner->accounts()->get();
        //     $this->assertDatabaseHas('cdrator_accounts', ['id' => 202006031246537161]);
        //     $this->assertEquals('1', $accounts[0]->account_type);
        //     $this->assertEquals('9009000', $accounts[0]->customer_number);
        //     $this->assertEquals($accounts[0]->cdrator_account_owner_id, $owner->id);

        //     // subscriptions (for account #202006031246537161)
        //     $subs = $accounts[0]->subscriptions()->get();
        //     $this->assertEquals(1, $subs->count());
        //     $this->assertEquals(202203161008157001, $subs[0]->id);
        //     $this->assertEquals(202006031246537161, $subs[0]->cdrator_account_id);
        //     $this->assertEquals('92688531', $subs[0]->phone_number);
        //     $this->assertEquals('2022-05-17', $subs[0]->establish_date);
        //     $this->assertEquals('2022-04-30', $subs[0]->start_date);
        //     $this->assertEquals(null, $subs[0]->end_date);
        //     $this->assertEquals('2022-03-30', $subs[0]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[0]->lockin_end_date);
        //     $this->assertEquals('24', $subs[0]->lockin_length);
        //     $this->assertEquals('2', $subs[0]->status_id);
        //     $this->assertEquals('200', $subs[0]->service_status);
        //     $this->assertEquals('242014511381549', $subs[0]->imsi);
        //     $this->assertEquals('nortelweb', $subs[0]->entered_by);

        //     // subscription bundles (for subscription #202203161008157001)
        //     $bundles = $subs[0]->bundles()->get();
        //     $this->assertEquals(1, $bundles->count());
        //     $this->assertEquals(202203161008157001, $subs[0]->id);
        //     $this->assertEquals(20220123, $bundles[0]->period);
        //     $this->assertEquals(10485760002, $bundles[0]->value_1);
        //     $this->assertEquals(1637826002, $bundles[0]->value_2);
        //     $this->assertEquals('DATA-SPLIT', $bundles[0]->code);
        //     $this->assertEquals('Nortel Grenseløs', $bundles[0]->name);

        //     ///
        //     /// #3 account owner
        //     ///
        //     $owner = CDRatorAccountOwner::find(202203141645005541);
        //     $this->assertDatabaseHas('cdrator_account_owners', ['id' => 202203141645005541]);
        //     $this->assertEquals('Ruben', $owner->first_name);
        //     $this->assertEquals('Steinen', $owner->last_name);
        //     $this->assertEquals('1955-04-17', $owner->date_of_birth);
        //     $this->assertEquals(null, $owner->company);
        //     $this->assertEquals('17045534555', $owner->uid);

        //     // addresses
        //     $addresses = $owner->addresses()->get();
        //     $this->assertEquals(1, $addresses->count());
        //     $this->assertEquals('Bulls Vei 2', $addresses[0]->address);
        //     $this->assertEquals('Åmot', $addresses[0]->city);
        //     $this->assertEquals('3340', $addresses[0]->zip);
        //     $this->assertEquals('Norge', $addresses[0]->country);

        //     // emails
        //     $emails = $owner->emails()->get();
        //     $this->assertEquals(1, $emails->count());
        //     $this->assertEquals('ru-st@online.no', $emails[0]->address);

        //     // accounts
        //     $accounts = $owner->accounts()->get();
        //     $this->assertDatabaseHas('cdrator_accounts', ['id' => 202203141645005542]);
        //     $this->assertEquals('1', $accounts[0]->account_type);
        //     $this->assertEquals('6006666', $accounts[0]->customer_number);
        //     $this->assertEquals($accounts[0]->cdrator_account_owner_id, $owner->id);

        //     // subscriptions (for account #202203141645005542)
        //     $subs = $accounts[0]->subscriptions()->get();
        //     $this->assertEquals(1, $subs->count());
        //     $this->assertEquals(202203161008157002, $subs[0]->id);
        //     $this->assertEquals(202203141645005542, $subs[0]->cdrator_account_id);
        //     $this->assertEquals('95801122', $subs[0]->phone_number);
        //     $this->assertEquals('2022-03-18', $subs[0]->establish_date);
        //     $this->assertEquals('2022-05-30', $subs[0]->start_date);
        //     $this->assertEquals(null, $subs[0]->end_date);
        //     $this->assertEquals('2022-05-30', $subs[0]->lockin_start_date);
        //     $this->assertEquals('2024-03-30', $subs[0]->lockin_end_date);
        //     $this->assertEquals('24', $subs[0]->lockin_length);
        //     $this->assertEquals('2', $subs[0]->status_id);
        //     $this->assertEquals('200', $subs[0]->service_status);
        //     $this->assertEquals('242124511381549', $subs[0]->imsi);
        //     $this->assertEquals('nortelweb', $subs[0]->entered_by);
    }
}
