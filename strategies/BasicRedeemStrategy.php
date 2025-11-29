<?php
namespace Fidelidade\Strategies;

class BasicRedeemStrategy implements RedeemStrategyInterface
{
    // 1 real para cada 100 pontos
    public function redeem(int $points): int
    {
        return intdiv($points, 100);
    }
}
