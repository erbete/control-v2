<?php

namespace Control\Common\Kernel;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

use Control\Rebinding\Jobs\RebindingCollectCustomerDataJob;

class Console extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('run:process')->dailyAt('05:00');
        $schedule->job(new RebindingCollectCustomerDataJob)->dailyAt('05:30');
        $schedule->job(new class implements ShouldQueue, ShouldBeUnique
        {
            use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
            public function handle()
            {
                DB::statement('VACUUM FULL');
            }
        })
            ->sundays()
            ->at('06:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    // protected function commands()
    // {
    //     $this->load(__DIR__ . '/../CSVDumpProcessing/Commands');
    //     // $this->load(__DIR__ . '\Rebinding\Commands');

    //     require base_path('src/Common/routes/console.php');
    // }

    protected $commands = [
        \Control\Processing\Commands\Processing::class,
        \Control\Infrastructure\Commands\Runner::class,
    ];
}
