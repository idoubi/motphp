<?php

namespace mot\controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response;
use mot\component\Request as MotRequest;
use mot\component\Response as MotResponse;

class Handler
{
    public function __construct(string $routePath, ContainerInterface $container)
    {
        spl_autoload_register(function ($class) {
            $classFile = ROOT_PATH . str_replace('\\', '/', $class) . '.php';
            if (is_file($classFile)) {
                include_once $classFile;
            }
        });

        $this->routePath = $routePath;
        $this->container = $container;
    }

    public function handle(Request $request, Response $response, array $args): Response
    {
        list($class, $method) = $this->getClassMethod($this->routePath);

        if (!class_exists($class)) {
            throw new \Exception('class not exists: ' . $class);
        }

        $obj = new $class($this->container);
        if (!method_exists($obj, $method)) {
            throw new \Exception('method not exists: ' . $class . ':' . $method);
        }

        if (isset($obj->middleware) && is_array($obj->middleware)) {
            foreach ($obj->middleware as $class) {
                if (class_exists($class)) {
                    (new $class)($request);
                }
            }
        }

        $res = $obj->$method($request, $response, $args);

        return $this->output($response, $res);
    }

    public function output($response, $res)
    {
        if ($res instanceof MotResponse) {
            return $res->innerResponse;
        }

        if ($res instanceof Response) {
            return $res;
        }

        $response->getBody()->write(strval($res));

        return $response;
    }

    public function getClassMethod(string $path): array
    {
        $pathArr = explode('/', $path);
        $classArr = [];
        $class = '';
        $method = '';

        $n = 0;
        $c = count($pathArr);

        foreach ($pathArr as $v) {
            $n++;

            if ($n == 2) {
                $classArr[] = 'app';
                $classArr[] = $v;
                $classArr[] = 'controller';
                continue;
            }

            if ($n == $c - 1) {
                $classArr[] = ucfirst($v);
                continue;
            }

            if ($n == $c) {
                $method = $v;
                continue;
            }

            $classArr[] = $v;
        }

        $class = implode('\\', $classArr);

        return [$class, $method];
    }
}
