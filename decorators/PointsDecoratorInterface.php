<?php
namespace Fidelidade\Decorators;

interface PointsDecoratorInterface
{
    public function apply(int $basePoints): int;
}
