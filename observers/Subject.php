<?php
namespace Fidelidade\Observers;

class Subject
{
    /** @var ObserverInterface[] */
    private array $observers = [];

    public function attach(ObserverInterface $o): void
    {
        $this->observers[] = $o;
    }

    public function detach(ObserverInterface $o): void
    {
        $this->observers = array_filter($this->observers, fn($x) => $x !== $o);
    }

    protected function notifyAll(string $event, array $payload = []): void
    {
        foreach ($this->observers as $o) {
            $o->notify($event, $payload);
        }
    }

    public function notify(string $event, array $payload = []): void
    {
        $this->notifyAll($event, $payload);
    }
}
