<?php
session_start();
require __DIR__ . '/../vendor/autoload.php';

use Fidelidade\Factory\StrategyFactory;
use Fidelidade\Domain\LoyaltyProgram;
use Fidelidade\Decorators\BonusMultiplierDecorator;
use Fidelidade\Observers\ObserverInterface;

if (!isset($_SESSION['state'])) {
    $_SESSION['state'] = [
        "tier" => "bronze",
        "points" => 0,
        "history" => []
    ];
}

$strategy = StrategyFactory::createAccumulation('tier', [
    'tier' => $_SESSION['state']['tier']
]);

$program = new LoyaltyProgram($strategy, $_SESSION['state']['tier']);

$reflection = new ReflectionClass($program);
$prop = $reflection->getProperty('points');
$prop->setAccessible(true);
$prop->setValue($program, $_SESSION['state']['points']);

class SessionObserver implements ObserverInterface {

    public function notify(string $event, array $payload): void {

        $_SESSION['state']['history'][] = [
            "event" => $event,
            "payload" => $this->cleanPayload($payload),
            "time" => date("H:i:s")
        ];
    }

    private function cleanPayload(array $payload): array
    {
        unset($payload['program']);
        return $payload;
    }
}

$program->attach(new \Fidelidade\Observers\ExpirationObserver());
$program->attach(new \Fidelidade\Observers\TierUpdateObserver());

$observer = new SessionObserver();
$program->attach($observer);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Resetar programa
    if (isset($_POST['resetar'])) {

        $_SESSION['state'] = [
            "tier" => "bronze",
            "points" => 0,
            "history" => []
        ];

        unset($_SESSION['last_result']);
        header("Location: index.php");
        exit;
    }

    $valor = floatval($_POST['valor']);
    $bonus = intval($_POST['bonus']);

    // Decorator
    $program->clearDecorators();

    if ($bonus > 0) {
        $program->addDecorator(new BonusMultiplierDecorator($bonus));
    }

    // Compra
    $gerados = $program->purchase($valor);

    $_SESSION['state']['points'] = $program->getPoints();
    $_SESSION['state']['tier'] = $program->getTier();

    $_SESSION['last_result'] = [
        "valor" => $valor,
        "pontos" => $gerados,
        "total" => $_SESSION['state']['points']
    ];
}

$state = $_SESSION['state'];
$last = $_SESSION['last_result'] ?? null;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Programa de Fidelidade</title>
    <style>
        body {
            font-family: Arial;
            max-width: 1200px;
            margin: auto;
            padding-top: 20px;
        }

        .layout {
            display: flex;
            gap: 20px;
        }

        .left {
            width: 50%;
        }

        .right {
            width: 50%;
        }

        .card {
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-top: 20px;
        }

        .history {
            background: #f7f7f7;
            padding: 10px;
            border-radius: 8px;
            height: 450px;
            overflow-y: scroll;
        }

    </style>
</head>
<body>

<div class="layout">
    <div class="left">

        <h2>Simulador de Programa de Fidelidade</h2>

        <form method="POST" style="margin-bottom:20px;">
            <input type="hidden" name="resetar" value="1">
            <button type="submit">
                Resetar Pontos
            </button>
        </form>

        <form method="POST">
            <label>Valor da compra:</label><br>
            <input type="number" step="0.01" name="valor" required><br><br>

            <label>Bônus (Decorator):</label><br>
            <select name="bonus">
                <option value="0">Nenhum</option>
                <option value="20">+20%</option>
                <option value="50">+50%</option>
                <option value="100">+100%</option>
            </select>

            <br><br>
            <button type="submit">Simular Compra</button>
        </form>

        <?php if ($last): ?>
        <div class="card">
            <h3>Resultado da Compra</h3>
            <p><strong>Valor:</strong> R$ <?= $last['valor'] ?></p>
            <p><strong>Pontos gerados:</strong> <?= $last['pontos'] ?></p>
            <p><strong>Total acumulado:</strong> <?= $last['total'] ?></p>
        </div>
        <?php endif; ?>

    </div>

    <div class="right">
        <div class="card">
            <h3>Histórico de Eventos</h3>
            <div class="history">
                <ul>
                    <?php foreach ($state['history'] as $item): ?>
                        <li>
                            <b>[<?= $item['time'] ?>]</b>
                            <?php
                                $event = $item['event'];
                                $p = $item['payload'];
                                $tierUsed = $p['tier_at_purchase'] ?? 'bronze';

                                switch ($event) {

                                    case 'points_accumulated':
                                        echo "+{$p['points']} pontos ";
                                        echo "(compra de R$ {$p['amount']}, tier {$tierUsed})";
                                        break;

                                    case 'points_expired':
                                        echo "Expiração automática: -{$p['expired']} pontos";
                                        break;

                                    case 'auto_tier_update':
                                        echo "Mudança automática de tier: {$p['from']} → {$p['to']}";
                                        break;

                                    case 'points_redeemed':
                                        echo "Resgate: -{$p['points']} pontos → recebeu {$p['value']}";
                                        break;

                                    default:
                                        echo "{$event}: " . json_encode($p);
                                }
                            ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<p style="margin-top:40px; font-size:12px; text-align:center">
    Desenvolvido por: João Pedro Pires Ferretti
</p>

</body>
</html>