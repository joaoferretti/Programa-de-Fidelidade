<?php
use PHPUnit\Framework\TestCase;

class SingletonTest extends TestCase
{
    public function testConfigIsSingleton()
    {
        $c1 = Fidelidade\Infra\Config::getInstance();
        $c2 = Fidelidade\Infra\Config::getInstance();
        $this->assertSame($c1, $c2);

        $c1->set('x', 123);
        $this->assertEquals(123, $c2->get('x'));
    }
}
