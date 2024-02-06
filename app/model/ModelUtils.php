<?php

namespace App\Model;

class ModelUtils
{
    public static function createQuery(...$colDefs): string
    {
        return join(
            ",",
            array_map(
                function ($colDef) {
                    [$column, $table, $isForeign, $alias] = is_array($colDef) ? array_pad($colDef, 4, null) : [
                        $colDef,
                        null,
                        null,
                        null
                    ];
                    return ($isForeign ? ':' : '') . ($table ? $table . '.' : '') . $column . ($alias ? " AS $alias" : "");
                },
                $colDefs,
            )
        );
    }
}