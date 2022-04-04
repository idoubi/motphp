<?php

function datetime(int $timestamp): string
{
    return date('Y-m-d H:i:s', intval($timestamp));
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

        return null;
    }
}


// function url($path, $params = [])
// {
//     if (stripos($path, '/') === 0) {
//         $url = $path;
//         if ($params && is_array($params)) {
//             $url .= '?' . http_build_query($params);
//         }

//         return $url;
//     }

//     $pathArr = explode('/', $path);
//     if (!$pathArr) {
//         return '';
//     }

//     $c = count($pathArr);
//     $urlArr = $pathArr;

//     if ($c == 1) {
//         $urlArr = [$this->app, $this->module, $this->controller, $pathArr[0]];
//     }

//     if ($c == 2) {
//         $urlArr = [$this->app, $this->module, ...$pathArr];
//     }

//     if ($c == 3) {
//         $urlArr = [$this->app, ...$pathArr];
//     }

//     $url = '/' . implode('/', $urlArr);
//     if ($params && is_array($params)) {
//         $url .= '?' . http_build_query($params);
//     }

//     return $url;
// }
