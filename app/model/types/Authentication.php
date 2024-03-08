<?php

namespace App\Model\Types;

use App\Model\Traits\PrimaryId;
use App\Model\Types\Base\DataType;

class Authentication extends DataType
{
    use PrimaryId;

    public string $code;
    public string $apiKey;
    public string $ip;
}