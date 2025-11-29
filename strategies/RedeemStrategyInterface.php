<?php
namespace Fidelidade\Strategies;

interface RedeemStrategyInterface
{
    //Converte pontos em valor
    public function redeem(int $points): int;
}
