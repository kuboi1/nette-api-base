<?php

namespace App\Model\Types;

use App\Model\Types\Base\LocalizedDataType;

class Article extends LocalizedDataType
{
    public ?string $image;

    // Translations
    public ?string $title;
    public ?string $text;
}