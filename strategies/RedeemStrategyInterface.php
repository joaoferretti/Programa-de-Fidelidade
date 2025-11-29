<?php
namespace Fidelidade\Strategies;

interface RedeemStrategyInterface
{
    /**
     * Converte pontos em valor (inteiro - centavos ou reais conforme convenção).
     *
     * @param int $points
     * @return int  Valor retornado (por exemplo, em reais ou centavos — seguir convenção do sistema)
     */
    public function redeem(int $points): int;
}
