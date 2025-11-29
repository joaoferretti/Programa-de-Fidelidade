<?php
namespace Fidelidade\Strategies;

class BasicRedeemStrategy implements RedeemStrategyInterface
{
    /**
     * Convenção simples: cada 100 pontos = 1 unidade monetária (ex: R$1).
     *
     * @param int $points
     * @return int
     */
    public function redeem(int $points): int
    {
        return intdiv($points, 100);
    }
}
