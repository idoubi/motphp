<?php

use Psr\Container\ContainerInterface;
use Selective\Config\Configuration;
use Illuminate\Database\Capsule\Manager as Capsule;
use Slim\Views\Twig;
use Slim\App;
use Slim\Factory\AppFactory;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

return [
    Configuration::class => function () {
        return new Configuration(require ROOT_PATH . 'config/settings.php');
    },

    Session::class => function (ContainerInterface $container) {
        $sessionSettings = $container->get(Configuration::class)->findArray('session');
        if (!$sessionSettings) {
            return null;
        }

        if (PHP_SAPI === 'cli') {
            return new Session(new MockArraySessionStorage());
        } else {
            return new Session(new NativeSessionStorage($sessionSettings));
        }
    },

    Capsule::class => function (ContainerInterface $container) {
        $dbSettings = $container->get(Configuration::class)->findArray('db');

        if (!$dbSettings) {
            return null;
        }

        $capsule = new Capsule();

        foreach ($dbSettings as $key => $settings) {
            $capsule->addConnection($settings, $key);
        }

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $capsule->connection()->enableQueryLog();

        return $capsule;
    },

    Twig::class => function () {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../theme/adminlte');
        $twig = new \Twig\Environment($loader, [
            // 'cache' => '/path/to/compilation_cache',
        ]);
        // return Twig::create(__DIR__ . '/../theme/adminlte', ['cache' => false]);

        return $twig;
    },

    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        return $app;
    },

];
