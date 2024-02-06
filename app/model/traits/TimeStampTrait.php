<?php

namespace App\Model\Traits;

use Nette\Utils\DateTime;

trait TimeStampTrait
{
    public ?DateTime $dateCreated = null;
    public ?DateTime $dateUpdated = null;
}