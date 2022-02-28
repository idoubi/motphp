<?php

namespace mot;

use DI\ContainerBuilder;
use Slim\App;
use mot\middleware\Paginate;
use mot\middleware\AutoRoute;

class Mot
{
    public static function start()
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->addDefinitions(__DIR__ . '/container.php');
        $container = $containerBuilder->build();

        $app = $container->get(App::class);
        $app->add(new Paginate());
        $app->add(new AutoRoute($container));

        try {
            $app->run();
        } catch (\Exception $e) {
            var_dump($e);
        }
    }
}
