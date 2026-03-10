<?php

namespace App\Console\Commands;

use App\Services\MonthlyTargetService;
use Illuminate\Console\Command;

class RecalculateMonthlyTargets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recalculate-monthly-targets
                            {--year= : The year to process (defaults to previous month\'s year)}
                            {--month= : The month to process (defaults to previous month)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate monthly target summaries for all active merchant locations';

    /**
     * Execute the console command.
     */
    public function handle(MonthlyTargetService $service): int
    {
        $previousMonth = now()->subMonth();
        $year = (int) ($this->option('year') ?? $previousMonth->format('Y'));
        $month = (int) ($this->option('month') ?? $previousMonth->format('m'));

        $this->info("Recalculating monthly targets for {$year}-{$month}...");

        $processed = $service->processMonthForAllLocations($year, $month);

        $this->info("Processed {$processed} merchant locations.");

        return self::SUCCESS;
    }
}
