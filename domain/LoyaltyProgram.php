<?php
namespace Fidelidade\Domain;

use Fidelidade\Observers\Subject;
use Fidelidade\Strategies\AccumulationStrategyInterface;
use Fidelidade\Strategies\RedeemStrategyInterface;
use Fidelidade\Decorators\PointsDecoratorInterface;

class LoyaltyProgram extends Subject
{
    private AccumulationStrategyInterface $strategy;

    /** @var PointsDecoratorInterface[] */
    private array $decorators = [];

    private int $points = 0;

    private string $tier = 'bronze';

    private ?RedeemStrategyInterface $redeemStrategy = null;

    public function __construct(AccumulationStrategyInterface $strategy, string $tier = 'bronze')
    {
        $this->strategy = $strategy;
        $this->tier = $tier;
    }

    public function setStrategy(AccumulationStrategyInterface $strategy): void
    {
        $this->strategy = $strategy;
    }

    public function setRedeemStrategy(RedeemStrategyInterface $redeemStrategy): void
    {
        $this->redeemStrategy = $redeemStrategy;
    }

    public function addDecorator(PointsDecoratorInterface $decorator): void
    {
        $this->decorators[] = $decorator;
    }

    public function clearDecorators(): void
    {
        $this->decorators = [];
    }

    public function setTier(string $tier): void
    {
        $this->tier = $tier;
    }

    public function getTier(): string
    {
        return $this->tier;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function increasePoints(int $pts): void
    {
        $this->points += $pts;
    }

    public function decreasePoints(int $pts): void
    {
        $this->points = max(0, $this->points - $pts);
    }

    public function previewPoints(float $amount): int
    {
        $base = $this->strategy->accumulate($amount);

        $final = array_reduce(
            $this->decorators,
            fn($carry, $decor) => $decor->apply($carry),
            $base
        );

        return (int) floor($final);
    }

    private function calculateTier(int $totalPoints): string
    {
        if ($totalPoints >= 5000) return 'platinum';
        if ($totalPoints >= 1000) return 'gold';
        return 'bronze';
    }

    public function purchase(float $amount): int
    {
        $potentialPoints = $this->previewPoints($amount);
        $futureTotal = $this->points + $potentialPoints;
        $newTier = $this->calculateTier($futureTotal);
        if ($newTier !== $this->tier) {
            $oldTier = $this->tier;
            $this->changeTier($newTier);
            $this->notify('auto_tier_update', [
                'from' => $oldTier,
                'to' => $newTier,
                'program' => $this
            ]);
        }
        return $this->accumulate($amount);
    }


    // acumular pontos
    public function accumulate(float $amount): int
    {
        $basePoints = $this->strategy->accumulate($amount);

        $finalPoints = array_reduce(
            $this->decorators,
            fn($carry, PointsDecoratorInterface $decor) => $decor->apply($carry),
            $basePoints
        );

        $finalPoints = (int) floor($finalPoints);

        $this->increasePoints($finalPoints);

        $this->notify('points_accumulated', [
            'points' => $finalPoints,
            'base'   => $basePoints,
            'amount' => $amount,
            'tier_at_purchase' => $this->tier,
            'program'=> $this
        ]);

        return $finalPoints;
    }


    // Resgate
    public function redeem(int $points): int
    {
        if ($points <= 0) {
            throw new \InvalidArgumentException("Pontos inválidos para resgate");
        }

        if ($points > $this->points) {
            throw new \Exception("Pontos insuficientes");
        }

        if ($this->redeemStrategy === null) {
            throw new \Exception("Redeem strategy não definida");
        }

        $value = $this->redeemStrategy->redeem($points);
        $this->decreasePoints($points);

        $this->notify('points_redeemed', [
            'points' => $points,
            'value' => $value,
            'program' => $this
        ]);

        return $value;
    }


    // Muda tier no strategy
    public function changeTier(string $newTier): void
    {
        $this->tier = $newTier;

        $this->setStrategy(
            \Fidelidade\Factory\StrategyFactory::createAccumulation('tier', [
                'tier' => $newTier
            ])
        );
    }

    public function expirePoints(int $percent): void
    {
        if ($percent <= 0) return;

        $expired = (int) floor($this->points * ($percent / 100));

        if ($expired <= 0) return;

        $this->decreasePoints($expired);

        $this->notify('points_expired', [
            'expired' => $expired,
            'reason' => "{$percent}_percent_manual_expiration",
            'program' => $this
        ]);
    }

}