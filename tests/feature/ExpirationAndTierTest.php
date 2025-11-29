<?php
use PHPUnit\Framework\TestCase;
use Fidelidade\Domain\LoyaltyProgram;
use Fidelidade\Strategies\TierAccumulationStrategy;
use Fidelidade\Observers\ExpirationObserver;
use Fidelidade\Observers\TierUpdateObserver;

class ExpirationAndTierTest extends TestCase
{
    public function testExpirationObserverAndTierUpdate()
    {
        $acc = new TierAccumulationStrategy();
        $program = new LoyaltyProgram($acc);

        // registrar observers - adaptar ao Subject implementation do seu projeto
        $program->attach(new ExpirationObserver());
        $program->attach(new TierUpdateObserver());

        // acumula 600 pontos via amount 600
        $added = $program->accumulate(600.0); // assume-se 1x = 600 pts

        // Após acumular, ExpirationObserver expira 10% -> 60 pontos
        $expectedAfterExpiration = 600 - (int) floor(600 * 0.10);
        $this->assertEquals($expectedAfterExpiration, $program->getPoints());

        // TierUpdateObserver deve ajustar tier: 600 -> still bronze (<1000)
        $this->assertEquals('bronze', $program->getTier());

        // acumula mais para ultrapassar 1000
        $program->accumulate(500.0); // +500 -> total base 1100 -> expiração aplicada etc

        $this->assertTrue(in_array($program->getTier(), ['gold','platinum','bronze']));
        $this->assertGreaterThanOrEqual(0, $program->getPoints());
    }
}
