<?php
namespace Fidelidade\Strategies;

interface AccumulationStrategyInterface
{
    public function accumulate(float $amount): int;
}
