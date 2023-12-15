<?php

namespace App\Model\Repositories;

use App\Model\Repositories\Base\Repository;
use App\Model\Types\Authentication;
use Nette\Database\Table\ActiveRow;

/**
 * @template-extends Repository<Authentication>
 */
class AuthenticationRepository extends Repository
{
    public const
        TABLE = 'authentication',

        COL_CODE = 'code',
        COL_API_KEY = 'api_key',
        COL_IP = 'ip',

        PARAM_API_ID = 'apiId',
        PARAM_API_KEY = 'apiKey';

    protected function mapToDataType(ActiveRow $row): Authentication
    {
        return new Authentication($row);
    }

    public function isValidAuth(string $code, string $apiKey, string $ip): bool
    {
        return (bool) $this->selectAll()
            ->where(self::COL_CODE, $code)
            ->where(self::COL_API_KEY, $apiKey)
            ->where(self::COL_IP, $ip)
            ->fetch();
    }
}