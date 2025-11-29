<?php
namespace Fidelidade\Observers;

use Fidelidade\Domain\LoyaltyProgram;

class ExpirationObserver implements ObserverInterface
{
    // expira 10% dos pontos recém-acumulados.
    public function notify(string $event, array $payload): void
    {
        if ($event !== 'points_accumulated') {
            return;
        }

        if (!isset($payload['program']) || !($payload['program'] instanceof LoyaltyProgram)) {
            return;
        }

        $program = $payload['program'];

        $points = (int) ($payload['points'] ?? 0);
        if ($points <= 0) {
            return;
        }

        $expired = (int) floor($points * 0.10);

        if ($expired > 0) {
            $program->decreasePoints($expired);
            $program->notify('points_expired', [
                'expired' => $expired,
                'reason' => '10_percent_on_accumulation_expiration',
                'program' => $program
            ]);
        }
    }
}