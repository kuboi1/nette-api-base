<?php

namespace App\Model\Types;

use App\Model\Traits\LocaleTrait;
use App\Model\Traits\PrimaryId;
use App\Model\Traits\TimeStampTrait;
use App\Model\Types\Base\DataType;

class Article extends DataType
{
    use PrimaryId, LocaleTrait, TimeStampTrait;

    public ?string $image;

    // Translations
    public ?string $title;
    public ?string $text;
}