<?php

namespace App\Console\Commands;

use App\Services\PayoutService;
use Illuminate\Console\Command;

class GroupPayouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:group-payouts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Group eligible deferred payouts into payout records on scheduled payout days';

    protected PayoutService $payoutService;

    public function __construct(PayoutService $payoutService)
    {
        parent::__construct();
        $this->payoutService = $payoutService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Log::info('Starting payout grouping process..');
        $this->info('Starting payout grouping process...');

        $result = $this->payoutService->groupEligiblePayouts();

        if ($result['success']) {
            $this->info($result['message']);
            $this->info("Payouts created: {$result['payouts_created']}");
            return Command::SUCCESS;
        } else {
            $this->warn($result['message']);
            return Command::SUCCESS;
        }
    }
}
