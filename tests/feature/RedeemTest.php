<?php
use PHPUnit\Framework\TestCase;
use Fidelidade\Domain\LoyaltyProgram;
use Fidelidade\Strategies\TierAccumulationStrategy;
use Fidelidade\Strategies\BasicRedeemStrategy;
use Fidelidade\Strategies\BlackFridayStrategy;
use Fidelidade\Decorators\BonusMultiplierDecorator;

class RedeemTest extends TestCase
{
    public function testAccumulateAndRedeemBasic()
    {
        $acc = new TierAccumulationStrategy();
        $program = new LoyaltyProgram($acc);
        $program->setRedeemStrategy(new BasicRedeemStrategy());

        $points = $program->accumulate(200.0);
        $this->assertGreaterThanOrEqual(200, $points);
        $this->assertEquals($program->getPoints(), $points);

        $value = $program->redeem(100);
        $this->assertEquals(1, $value);
        $this->assertEquals($points - 100, $program->getPoints());
    }

    public function testCampaignStrategyBlackFriday()
    {
        $black = new BlackFridayStrategy();
        $program = new LoyaltyProgram($black);
        $program->setRedeemStrategy(new BasicRedeemStrategy());

        $points = $program->accumulate(100.0);
        $this->assertEquals(500, $points);
    }
}
