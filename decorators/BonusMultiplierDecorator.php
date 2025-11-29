<?php
namespace Fidelidade\Decorators;

class BonusMultiplierDecorator implements PointsDecoratorInterface
{
    private int $multiplierPercent;

    public function __construct(int $multiplierPercent)
    {
        $this->multiplierPercent = $multiplierPercent;
    }

    public function apply(int $basePoints): int
    {
        return (int) floor($basePoints * (1 + $this->multiplierPercent / 100));
    }
}
