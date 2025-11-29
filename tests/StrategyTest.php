<?php
use PHPUnit\Framework\TestCase;
use Fidelidade\Factory\StrategyFactory;
use Fidelidade\Domain\LoyaltyProgram;

class StrategyTest extends TestCase
{
    public function testStrategySwapChangesPoints()
    {
        $tier = StrategyFactory::createAccumulation('tier', ['tier' => 'bronze']);
        $program = new Fidelidade\Domain\LoyaltyProgram($tier, 'bronze');

        $pts1 = $program->purchase(100);
        $this->assertEquals(100, $pts1);

        $program->setStrategy(StrategyFactory::createAccumulation('tier', ['tier' => 'gold']));
        $pts2 = $program->purchase(100);
        $this->assertEquals(200, $pts2);
    }
}
