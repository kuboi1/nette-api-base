<?php

namespace App\services;

class ConfigService
{
    private const
        CONFIG_KEYS = 'keys';

    public const
        CONFIG_KEYS_API = 'api',
        CONFIG_KEYS_MASTER_KEY = 'masterKey';

    public function __construct(
        private readonly array $parameters
    )
    {}

    public function getKeys(): object
    {
        return (object) $this->parameters[self::CONFIG_KEYS];
    }

    public function getApiMasterKey(): string
    {
        return $this->getKeys()->{self::CONFIG_KEYS_API}[self::CONFIG_KEYS_MASTER_KEY];
    }
}