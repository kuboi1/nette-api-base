<?php

namespace App\Model\Types;

use App\Model\Types\Base\DataType;

class Authentication extends DataType
{
    public string $code;
    public string $apiKey;
    public string $ip;
}