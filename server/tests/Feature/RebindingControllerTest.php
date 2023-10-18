<?php

namespace Tests\Feature;

use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Tests\TestCase;

use Control\Infrastructure\User;
use Database\Seeders\TestDatabaseSeeder;

class RebindingControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function test_index()
    {
        //     // Arrange
        //     $this->artisan('cdrator:process')->assertSuccessful();
        //     $this->seed(TestDatabaseSeeder::class);
        //     $this->postJson('/login', ['email' => 'dev@user.no', 'password' => 'password']);
        //     $user = User::where('email', 'dev@user.no')->first();

        //     $account = CDRatorAccount::find(202203161008083901);
        //     $rebinding = new CDRatorRebinding(['status' => 'rebinded', 'note' => 'test']);
        //     $rebinding->user_id = $user->id;
        //     $rebinding->cdrator_account_id = $account->id;
        //     $rebinding->save();

        //     $status = 'rebinded';
        //     $lockinFromMonth = '2020-01-01';
        //     $lockinToMonth = '2030-01-01';

        //     // Act
        //     $response = $this->getJson('/api/rebinding?status=' . $status . '&lockinFromMonth=' . $lockinFromMonth . '&lockinToMonth=' . $lockinToMonth);

        //     // Assert
        //     $response
        //         ->assertOk()
        //         ->assertJson(
        //             fn (AssertableJson $json) =>
        //             $json->has(1)->first(
        //                 fn ($json) => $json
        //                     ->where('uid', '927106421')
        //                     ->where('accountId', '202203161008083901')
        //                     ->where('company', 'Stortransport AS')
        //                     ->where('lockinEndDate', '2024-03-30')
        //                     ->where('lockinLength', '24')
        //                     ->where('subCount', '26')
        //                     ->where('status', 'rebinded')
        //                     ->where('note', $rebinding->note)
        //                     ->where('rebindedAt', $rebinding->rebindedAt)
        //                     ->where('revenue.202200.totalRevenue', '2600.10')
        //                     ->where('revenue.202200.totalTrafficCost', '1300.10')
        //                     ->where('revenue.202200.voice', '32084')
        //                     ->where('revenue.202200.dg', '50')
        //                     ->etc()
        //             )
        //         );
        // }

        // public function test_forbidden_if_permission_not_set()
        // {
        //     // Arrange
        //     User::create([
        //         'name' => 'testing',
        //         'email' => 'test@epost.xyz',
        //         'password' => 'password',
        //         'blocked' => false,
        //     ]);
        //     $this->postJson('/login', ['email' => 'test@epost.xyz', 'password' => 'password']);
        //     $status = 'rebinded';
        //     $lockinFromMonth = '2020-01-01';
        //     $lockinToMonth = '2030-01-01';

        //     // Act
        //     $response = $this->getJson('/api/rebinding?status=' . $status . '&lockinFromMonth=' . $lockinFromMonth . '&lockinToMonth=' . $lockinToMonth);

        //     // Assert
        //     $response->assertStatus(Response::HTTP_FORBIDDEN)
        //         ->assertJson(
        //             fn (AssertableJson $json) =>
        //             $json
        //                 ->where('title', 'Forbudt')
        //                 ->where('detail', 'Brukeren har ugyldig tilgang.')
        //                 ->etc()
        //         );
        // }

        // public function test_details()
        // {
        //     // Arrange
        //     $this->artisan('cdrator:process')->assertSuccessful();
        //     $this->seed(TestDatabaseSeeder::class);
        //     $accountId = 202203161008083901;
        //     $this->postJson('/login', ['email' => 'dev@user.no', 'password' => 'password']);

        //     // Act
        //     $response = $this->getJson('/api/rebinding/details?accountId=' . $accountId);

        //     // Assert
        //     $response
        //         ->assertOk()
        //         ->assertJson(
        //             fn (AssertableJson $json) =>
        //             $json->has(26)->first(
        //                 fn ($json) =>
        //                 $json
        //                     ->where('phoneNumber', '96424821')
        //                     ->where('lockinEndDate', '2024-03-30')
        //                     ->where('priceplan', 'Flex 1GB')
        //                     ->where('periodUsageDetails.202200.subscriptionFeesRevenue', '205')
        //                     ->where('periodUsageDetails.202200.gbDataUsage', '0.93')
        //                     ->where('periodUsageDetails.202200.totalTalkTimeMinutes', '20')
        //                     ->where('periodUsageDetails.202200.db', '50.00')
        //                     ->etc()
        //             )
        //         );
    }
}
