<?php
namespace Fidelidade\Observers;

use Fidelidade\Domain\LoyaltyProgram;

class ExpirationObserver implements ObserverInterface
{
    /**
     * Espera payload com:
     * - 'program' => LoyaltyProgram (instância)
     * - 'points' => int (se aplicável)
     * - 'event' => string
     *
     * Aqui implementamos comportamento simples: quando houver 'points_accumulated' expiramos 10% dos pontos recém-acumulados.
     */
    public function notify(string $event, array $payload): void
    {
        // Só age sobre accumulation
        if ($event !== 'points_accumulated') {
            return;
        }

        if (!isset($payload['program']) || !($payload['program'] instanceof LoyaltyProgram)) {
            return;
        }

        /** @var LoyaltyProgram $program */
        $program = $payload['program'];

        $points = (int) ($payload['points'] ?? 0);
        if ($points <= 0) {
            return;
        }

        // regra simples de expiração: expira 10% dos pontos acumulados em cada acúmulo
        $expired = (int) floor($points * 0.10);

        if ($expired > 0) {
            $program->decreasePoints($expired);
            // notifica que expiração ocorreu
            $program->notify('points_expired', [
                'expired' => $expired,
                'reason' => '10_percent_on_accumulation_expiration',
                'program' => $program
            ]);
        }
    }
}
