<?php

namespace App\Model;

use App\Model\Base\Repository;
use Nette\Database\Explorer;

class AuthenticationRepository extends Repository
{
    public const
        TABLE = 'authentication',

        COL_CODE = 'code',
        COL_API_KEY = 'api_key',
        COL_IP = 'ip',

        PARAM_API_ID = 'apiId',
        PARAM_API_KEY = 'apiKey';

    public function __construct(Explorer $database)
    {
        parent::__construct($database);

        self::$table = self::TABLE;
    }

    public function isValidAuth(string $code, string $apiKey, string $ip): bool
    {
        return (bool) $this->findAll()
            ->where(self::COL_CODE, $code)
            ->where(self::COL_API_KEY, $apiKey)
            ->where(self::COL_IP, $ip)
            ->fetch();
    }
}