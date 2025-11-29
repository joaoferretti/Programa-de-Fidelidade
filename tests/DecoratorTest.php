<?php
require __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Fidelidade\Strategies\TierAccumulationStrategy;
use Fidelidade\Domain\LoyaltyProgram;
use Fidelidade\Decorators\BonusMultiplierDecorator;

class DecoratorTest extends TestCase
{
    public function testBonusMultiplierApplies()
    {
        $strategy = new TierAccumulationStrategy('bronze');
        $program = new LoyaltyProgram($strategy);

        $program->addDecorator(new BonusMultiplierDecorator(50));
        $pts = $program->purchase(100);

        $this->assertEquals(150, $pts);
    }
}
