<?php
namespace Fidelidade\Strategies;

class CampaignAccumulationStrategy implements AccumulationStrategyInterface
{
    private float $multiplier;

    public function __construct(float $multiplier = 1.0)
    {
        $this->multiplier = $multiplier;
    }

    public function setMultiplier(float $m): void
    {
        $this->multiplier = $m;
    }

    public function accumulate(float $amount): int
    {
        return (int) floor($amount * $this->multiplier);
    }
}
