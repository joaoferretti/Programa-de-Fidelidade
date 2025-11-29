<?php
namespace Fidelidade\Observers;

interface ObserverInterface
{
    public function notify(string $event, array $payload): void;
}
