<?php
namespace Fidelidade\Strategies;

class BlackFridayStrategy implements AccumulationStrategyInterface
{
    public function accumulate(float $amount): int
    {
        // 5x
        return (int) floor($amount * 5);
    }
}