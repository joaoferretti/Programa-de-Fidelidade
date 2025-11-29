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


    /* ============================================================
       PRÉ-VISUALIZAÇÃO DE PONTOS (SEM APLICAR)
    ============================================================ */
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


    /* ============================================================
       REGRAS DE TIER (AGORA INTERNAS)
    ============================================================ */
    private function calculateTier(int $totalPoints): string
    {
        if ($totalPoints >= 5000) return 'platinum';
        if ($totalPoints >= 1000) return 'gold';
        return 'bronze';
    }


    /* ============================================================
       APLICAÇÃO DE COMPRA (PROMOVE ANTES DE ACUMULAR)
    ============================================================ */
    public function purchase(float $amount): int
    {
        // 1. prever pontos que seriam gerados
        $potentialPoints = $this->previewPoints($amount);

        // 2. calcular pontos totais após a compra teórica
        $futureTotal = $this->points + $potentialPoints;

        // 3. descobrir tier futuro
        $newTier = $this->calculateTier($futureTotal);

        // 4. se houver mudança, aplicar ANTES de acumular
        if ($newTier !== $this->tier) {
            $oldTier = $this->tier;

            // muda internamente (isso muda também a strategy)
            $this->changeTier($newTier);

            // dispara evento
            $this->notify('auto_tier_update', [
                'from' => $oldTier,
                'to' => $newTier,
                'program' => $this
            ]);
        }

        // 5. finalmente acumula usando o novo tier
        return $this->accumulate($amount);
    }


    /* ============================================================
       ACUMULAR PONTOS (AGORA COM tier_at_purchase)
    ============================================================ */
    public function accumulate(float $amount): int
    {
        // valor base
        $basePoints = $this->strategy->accumulate($amount);

        // aplica decoradores
        $finalPoints = array_reduce(
            $this->decorators,
            fn($carry, PointsDecoratorInterface $decor) => $decor->apply($carry),
            $basePoints
        );

        $finalPoints = (int) floor($finalPoints);

        // incrementa
        $this->increasePoints($finalPoints);

        // envia tier_at_purchase para o histórico
        $this->notify('points_accumulated', [
            'points' => $finalPoints,
            'base'   => $basePoints,
            'amount' => $amount,
            'tier_at_purchase' => $this->tier,
            'program'=> $this
        ]);

        return $finalPoints;
    }


    /* ============================================================
       RESGATE
    ============================================================ */
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


    /* ============================================================
       MUDAR TIER (ATUALIZA STRATEGY AUTOMATICAMENTE)
    ============================================================ */
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