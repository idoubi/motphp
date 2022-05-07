<?php

use DI\ContainerBuilder;
use Slim\App;
use Illuminate\Database\Capsule\Manager as Capsule;

function getContainer()
{
    $containerBuilder = new ContainerBuilder();
    $containerBuilder->addDefinitions(__DIR__ . '/container.php');
    $container = $containerBuilder->build();

    return $container;
}

function getApp()
{
    $app = getContainer()->get(App::class);

    return $app;
}

function getRequest()
{
    $request = getApp()->request;

    return $request;
}

function getDb()
{
    $db = getContainer()->get(Capsule::class);

    return $db;
}

function datetime($timestamp): string
{
    if (is_string($timestamp)) {
        $timestamp = strtotime($timestamp);
    }

    return date('Y-m-d H:i:s', $timestamp);
}

function genOrderid($prefix = '')
{
    $orderid = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

    return sprintf("%s%s", $prefix, $orderid);
}

function nonceStr(int $len = 32): string
{
    $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $nonce = '';
    for ($i = 0; $i < $len; $i++) {
        $nonce .= $str[mt_rand(0, 61)];
    }

    return $nonce;
}

function parseKv(string $str, string $lineSep = "\r\n", string $kvSep = ':')
{
    $options = [];
    $lines = explode($lineSep, $str);
    foreach ($lines as $k => $line) {
        if (empty($line)) {
            continue;
        }
        $arr = explode($kvSep, $line, 2);
        if (!$arr) {
            continue;
        }
        if (count($arr) == 1) {
            $options[$k] = $arr[0];
            continue;
        }

        $options[$arr[0]] = $arr[1];
    }

    return $options;
}

function getAppName(): string
{
    return '';
}

function getUrlInfo(): array
{
    header('Content-Type: application/json');
    echo json_encode($_SERVER);
    die;
    return [];
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $status = 302)
    {
        header("Location: $url", true, $status);
        die;
    }
}

if (!function_exists('model')) {
    function model(string $path)
    {
        $pathArr = explode('/', $path);
        $pathArr = array_filter($pathArr);

        if (!$pathArr || count($pathArr) < 2) {
            return null;
        }

        $classArr = $pathArr;

        array_unshift($classArr, '\app');
        array_splice($classArr, 2, 0, 'model');

        $class = implode('\\', $classArr);

        if (class_exists($class)) {
            return new $class();
        }

        // auto build table name
        $tableName = str_replace('/', '_', strtolower($path));

        return getDb()->table($tableName);
    }
}


function url($path, $params = [])
{
    $request = getRequest();
    $uri = $request->getUri();
    $path = $uri->getPath();
    $query = $uri->getQuery();
    $pathArr = array_filter(explode('/', $path));
    $n = 0;
    $c = count($pathArr);
    $modArr = [];
    $nodeArr = [];

    $app = '';
    $module = '';
    $controller = '';
    $action = '';

    foreach ($pathArr as $v) {
        $n++;
        if ($n == 1) {
            $app = $v;
            continue;
        }
        $nodeArr[] = $v;
        if ($n == $c - 1) {
            $controller = $v;
            continue;
        }
        if ($n == $c) {
            $action = $v;
            continue;
        }

        $modArr[] = $v;
    }

    $fullpath = $path;
    if ($query) {
        $fullpath .= '?' . $query;
    }
    $module = implode('/', $modArr);
    $node = implode('/', $nodeArr);

    if (stripos($path, '/') === 0) {
        $url = $path;
        if ($params && is_array($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    $pathArr = explode('/', $path);
    if (!$pathArr) {
        return '';
    }

    $c = count($pathArr);
    $urlArr = $pathArr;

    if ($c == 1) {
        $urlArr = [$app, $module, $controller, $pathArr[0]];
    }

    if ($c == 2) {
        $urlArr = [$app, $module, ...$pathArr];
    }

    if ($c == 3) {
        $urlArr = [$app, ...$pathArr];
    }

    $url = '/' . implode('/', $urlArr);
    if ($params && is_array($params)) {
        $url .= '?' . http_build_query($params);
    }

    return $url;
}
