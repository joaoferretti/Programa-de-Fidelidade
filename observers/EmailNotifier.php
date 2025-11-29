<?php
namespace Fidelidade\Observers;

class EmailNotifier implements ObserverInterface
{
    private string $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function notify(string $event, array $payload): void
    {
        echo "[EmailNotifier] Para: {$this->email} | Evento: {$event} | Dados: " . json_encode($payload) . PHP_EOL;
    }
}
