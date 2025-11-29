<?php
use PHPUnit\Framework\TestCase;

class ObserverTest extends TestCase
{
    public function testObserversAreNotified()
    {
        $strategy = new Fidelidade\Strategies\TierAccumulationStrategy('bronze');
        $program = new Fidelidade\Domain\LoyaltyProgram($strategy);

        $fake = new class implements Fidelidade\Observers\ObserverInterface {
            public array $events = [];
            public function notify(string $event, array $payload): void
            {
                $this->events[] = ['event' => $event, 'payload' => $payload];
            }
        };

        $program->attach($fake);
        $program->purchase(10);
        $this->assertNotEmpty($fake->events);
        $this->assertEquals('points_accumulated', $fake->events[0]['event']);
    }
}
