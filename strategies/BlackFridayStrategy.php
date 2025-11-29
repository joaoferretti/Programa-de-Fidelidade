<?php
namespace Fidelidade\Strategies;

class BlackFridayStrategy implements AccumulationStrategyInterface
{
    public function accumulate(float $amount): int
    {
        // 5x (500%)
        return (int) floor($amount * 5);
    }
}