<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;

class ProcessOrderLifecycle extends Command
{
    protected $signature = 'orders:process-lifecycle';

    protected $description = 'Auto reject expired orders';

    public function handle()
    {
        $updated = Order::where('status', 'pending')
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', now())
            ->update([
                'status' => 'ditolak'
            ]);

        $this->info("Order expired: " . $updated);

        return 0;
    }
}