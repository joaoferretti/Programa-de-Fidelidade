<?php
namespace Fidelidade\Strategies;

class TierAccumulationStrategy implements AccumulationStrategyInterface
{
    private string $tier;

    public function __construct(string $tier = 'bronze')
    {
        $this->tier = $tier;
    }

    public function setTier(string $tier): void
    {
        $this->tier = $tier;
    }

    public function accumulate(float $amount): int
    {
        return match($this->tier) {
            'platinum' => (int) floor($amount * 3),
            'gold' => (int) floor($amount * 2),
            default => (int) floor($amount * 1),
        };
    }
}
