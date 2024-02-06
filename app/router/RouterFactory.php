<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;

final class RouterFactory
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList;

        // API Routes
        $router->withModule('Api')
            ->addRoute('[<locale=en en|cs>/]<presenter>[/<action>][/<id>]', 'Base:default');

        return $router;
    }

}