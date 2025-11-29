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

        $program->attach(new ExpirationObserver());
        $program->attach(new TierUpdateObserver());

        $added = $program->accumulate(600.0); 

        $expectedAfterExpiration = 600 - (int) floor(600 * 0.10);
        $this->assertEquals($expectedAfterExpiration, $program->getPoints());

        $this->assertEquals('bronze', $program->getTier());

        $program->accumulate(500.0);

        $this->assertTrue(in_array($program->getTier(), ['gold','platinum','bronze']));
        $this->assertGreaterThanOrEqual(0, $program->getPoints());
    }
}
