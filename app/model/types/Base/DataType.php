<?php

namespace App\Model\Types\Base;

use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;

/**
 * @template T
 */
abstract class DataType
{
    public int $id;
    public DateTime $dateCreated;
    public DateTime $dateUpdated;

    public function __construct(ActiveRow $row)
    {
        foreach ($row->toArray() as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}