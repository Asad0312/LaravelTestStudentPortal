<?php

namespace App\Console\Commands;

use App\Services\FeeCarryForward;
use Carbon\Carbon;
use Illuminate\Console\Command;
use InvalidArgumentException;

class GenerateMonthlyFees extends Command
{
    protected $signature = 'fees:generate {month? : Month in YYYY-MM format (defaults to current month)}';

    protected $description = 'Create monthly fee rows for active students and carry forward unpaid balances';

    public function handle(FeeCarryForward $fees): int
    {
        try {
            $month = $fees->month($this->argument('month'));
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $result = $fees->generate($month);
        $label = Carbon::createFromFormat('Y-m-d', $month . '-01')->format('F Y');

        $this->info("Fee rows for {$label}: {$result['created']} created, {$result['skipped']} already present.");
        if ($result['carried'] > 0) {
            $this->line('Carried forward from previous months: Rs. ' . number_format($result['carried']));
        }

        return self::SUCCESS;
    }
}
