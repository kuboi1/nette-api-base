<?php

namespace App\Model\Types\Base;

use Nette\Database\Table\ActiveRow;

/**
 * @template T
 */
abstract class DataType
{
    public function __construct(ActiveRow|array $row)
    {
        foreach ((is_array($row) ? $row : $row->toArray()) as $key => $value) {
            $key = $this->camelize($key);
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    private function camelize(string $string): string
    {
        return lcfirst(
            str_replace(' ', '', ucwords(str_replace('_', ' ', $string)))
        );
    }
}