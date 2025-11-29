<?php
namespace Fidelidade\Strategies;

class WeekendStrategy implements AccumulationStrategyInterface
{
    public function accumulate(float $amount): int
    {
        // 2x no fim de semana
        return (int) floor($amount * 2);
    }
}