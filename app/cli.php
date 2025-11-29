<?php
require __DIR__ . '/../vendor/autoload.php';

use Fidelidade\Factory\StrategyFactory;
use Fidelidade\Domain\LoyaltyProgram;
use Fidelidade\Decorators\BonusMultiplierDecorator;
use Fidelidade\Observers\EmailNotifier;
use Fidelidade\Infra\Config;

$cfg = Config::getInstance();

function footer() {
    echo "\n---\nDesenvolvido por: João Pedro Pires Ferretti\n";
}

echo "Programa de Fidelidade - Demo CLI\n\n";

$strategy = StrategyFactory::createAccumulation('tier', ['tier' => 'gold']);
$program = new LoyaltyProgram($strategy, 'gold');

$program->attach(new \Fidelidade\Observers\ExpirationObserver());
$program->attach(new \Fidelidade\Observers\TierUpdateObserver());

$notifier = new EmailNotifier('cliente@example.com');
$program->attach($notifier);

$program->addDecorator(new BonusMultiplierDecorator(50));

$amount = 120.50;
$gained = $program->purchase($amount);

echo "Compra de R$ {$amount} gerou {$gained} pontos. Total: {$program->getPoints()}\n";

$program->setStrategy(StrategyFactory::createAccumulation('campaign', ['multiplier' => 3.0]));
$program->clearDecorators();
$gained2 = $program->purchase(50);

echo "Compra em campanha → pontos: {$gained2}. Total: {$program->getPoints()}\n";

$program->expirePoints(10);

footer();
