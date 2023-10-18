<?php

namespace Control\Infrastructure\Commands;

use Illuminate\Console\Command;

class Runner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:runner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run whatever job you want here';

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
        \Control\Rebinding\Jobs\RebindingCollectCustomerDataJob::dispatch();
        return 0;
    }
}
