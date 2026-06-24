<?php

namespace App\Console\Commands;

use App\Services\Printing\EscposPrinter;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Mike42\Escpos\Printer;

class EscposTestPrint extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'escpos:test {message? : Optional message to print}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test ESC/POS print to the configured receipt printer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $message = (string) ($this->argument('message') ?: 'Hello from SMARTACCOUNTING');
        $jobId = (string) Str::uuid();

        $escpos = new EscposPrinter();
        $target = $escpos->targetLabel();

        $this->info("Printing ESC/POS test slip to {$target} ...");

        try {
            $escpos->run(function (Printer $p) use ($message, $jobId): void {
                $p->setEmphasis(true);
                $p->text("ESC/POS TEST PRINT\n");
                $p->setEmphasis(false);
                $p->text("Job: {$jobId}\n");
                $p->text("At: " . now()->format('Y-m-d H:i:s') . "\n");
                $p->feed();
                $p->text($message . "\n");
                $p->feed(3);
                $p->cut();
            });
        } catch (\Throwable $e) {
            $this->error('Print failed: ' . $e->getMessage());
            if (PHP_OS_FAMILY === 'Windows') {
                $this->line('Hint: set ESCPOS_WINDOWS_PRINTER in .env to the exact name from Windows Settings → Printers.');
            }

            return Command::FAILURE;
        }

        $this->info("Done. Job id: {$jobId}");

        return Command::SUCCESS;
    }
}
