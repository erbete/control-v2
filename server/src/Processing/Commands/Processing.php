<?php

namespace Control\Processing\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

use Control\Processing\Jobs\ProcessErateAccountsJob;
use Control\Processing\Jobs\ProcessErateNPExportJob;
use Control\Processing\Jobs\ProcessErateNPImportJob;
use Control\Processing\Jobs\ProcessErateSubscriptionsJob;
use Control\Processing\Jobs\ProcessErateUsersJob;
use Control\Processing\Jobs\ProcessAccountOwnersJob;
use Control\Processing\Jobs\ProcessAccountsJob;
use Control\Processing\Jobs\ProcessCustomerExpendituresJob;
use Control\Processing\Jobs\ProcessErateCustomerRevenueFigJob;
use Control\Processing\Jobs\ProcessSubscriptionsJob;
use Control\Processing\Jobs\ProcessTelavoxUsersJob;
use Control\Processing\Jobs\ProcessTelavoxCustomersJob;
use Control\Processing\Jobs\ProcessTelavoxSubscriptionsJob;

class Processing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the file dumps';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Bus::batch([
            new ProcessErateAccountsJob,
            new ProcessErateUsersJob,
            new ProcessErateSubscriptionsJob,
            new ProcessErateNPExportJob,
            new ProcessErateNPImportJob,
            new ProcessErateCustomerRevenueFigJob,
            new ProcessTelavoxUsersJob,
            new ProcessTelavoxCustomersJob,
            new ProcessTelavoxSubscriptionsJob,
        ])
            ->then(function () {
                ProcessAccountOwnersJob::dispatch()->onQueue('processing');
                ProcessAccountsJob::dispatch()->onQueue('processing');
                ProcessSubscriptionsJob::dispatch()->onQueue('processing');
                ProcessCustomerExpendituresJob::dispatch()->onQueue('processing');
            })
            ->name('Processing')
            ->onQueue('processing')
            ->dispatch();

        return 0;
    }
}
