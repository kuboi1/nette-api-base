<?php

namespace App\Model\Types\Base;

abstract class LocalizedDataType extends DataType
{
    public string $locale;
}