
<?php

namespace Modules\Gallface\Console;

use Illuminate\Console\Command;
use App\Transaction;

class TestEventCommand extends Command
{
    protected $signature = 'gallface:test-event {transaction_id}';
    protected $description = 'Test if sale events are firing for a transaction';

    public function handle()
    {
        $transactionId = $this->argument('transaction_id');
        
        $transaction = Transaction::find($transactionId);
        
        if (!$transaction) {
            $this->error("Transaction not found");
            return 1;
        }
        
        $this->info("Testing event for transaction: {$transaction->invoice_no}");
        
        // Fire the event manually
        event(new \App\Events\SellCreatedOrModified($transaction));
        
        $this->info("Event fired! Check logs at storage/logs/laravel.log");
        
        return 0;
    }
}
