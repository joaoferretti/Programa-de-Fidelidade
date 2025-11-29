<?php
namespace Fidelidade\Observers;

use Fidelidade\Domain\LoyaltyProgram;

class TierUpdateObserver implements ObserverInterface
{
    public function notify(string $event, array $payload): void
    {
        if ($event !== 'points_accumulated') {
            return;
        }

        /** @var LoyaltyProgram $program */
        $program = $payload['program'];

        $points = $program->getPoints();

        $newTier = match (true) {
            $points >= 5000 => 'platinum',
            $points >= 1000 => 'gold',
            default => 'bronze',
        };

        if ($newTier !== $program->getTier()) {

            $oldTier = $program->getTier();

            // Atualiza LoyaltyProgram
            $program->changeTier($newTier);

            // Atualiza sessão
            $_SESSION['state']['tier'] = $newTier;

            // Dispara evento público
            $program->notify('auto_tier_update', [
                'from' => $oldTier,
                'to'  => $newTier
            ]);
        }
    }
}
