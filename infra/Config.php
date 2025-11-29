<?php
namespace Fidelidade\Infra;

class Config
{
    private static ?self $instance = null;
    private array $data;

    private function __construct()
    {
        $this->data = [
            'currency' => 'BRL',
            'default_tier' => 'bronze'
        ];
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }
}
