<?php

namespace App\Console\Commands;

use App\Services\ReservationService;
use Illuminate\Console\Command;

class ExpireStockReservations extends Command
{
    protected $signature = 'orders:expire-reservations';
    protected $description = 'Release expired stock reservations back to available inventory';

    public function handle(ReservationService $reservationService): int
    {
        $count = $reservationService->releaseExpiredReservations();
        $this->info("Released {$count} expired stock reservations.");
        return Command::SUCCESS;
    }
}
