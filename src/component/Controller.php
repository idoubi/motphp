<?php

namespace mot\component;

use Selective\Config\Configuration;
use Illuminate\Database\Capsule\Manager as Capsule;

class Controller
{
    public function __construct($container)
    {
        $this->container = $container;

        $this->request = $container->get('request');
        $this->response = $container->get('response');
        $this->view = $this->container->get('view');
        $this->session = $this->container->get('session');

        $this->db = $container->get(Capsule::class);
        $this->config = $container->get(Configuration::class);

        $this->parseNode();
    }

    public function parseNode()
    {
        $uri = $this->request->getUri();
        $path = $uri->getPath();
        $query = $uri->getQuery();
        $pathArr = array_filter(explode('/', $path));
        $n = 0;
        $c = count($pathArr);
        $modArr = [];
        $nodeArr = [];
        foreach ($pathArr as $v) {
            $n++;
            if ($n == 1) {
                $this->app = $v;
                continue;
            }
            $nodeArr[] = $v;
            if ($n == $c - 1) {
                $this->controller = $v;
                continue;
            }
            if ($n == $c) {
                $this->action = $v;
                continue;
            }

            $modArr[] = $v;
        }

        $this->path = $path;
        $this->fullpath = $path;
        if ($query) {
            $this->fullpath .= '?' . $query;
        }
        $this->module = implode('/', $modArr);
        $this->node = implode('/', $nodeArr);
    }

    public function model($path)
    {
        $pathArr = explode('/', $path);
        $pathArr = array_filter($pathArr);

        if (!$pathArr || count($pathArr) < 1) {
            return null;
        }

        if (count($pathArr) == 1) {
            $pathArr = [$this->app, $pathArr[0]];
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

    public function url($path, $params = [])
    {
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
            $urlArr = [$this->app, $this->module, $this->controller, $pathArr[0]];
        }

        if ($c == 2) {
            $urlArr = [$this->app, $this->module, ...$pathArr];
        }

        if ($c == 3) {
            $urlArr = [$this->app, ...$pathArr];
        }

        $url = '/' . implode('/', $urlArr);
        if ($params && is_array($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    public function redirect($url, $status = 302)
    {
        header("Location: $url", true, $status);
        die;
    }

    public function redirectAppendParams($params = [], $status = 302)
    {
        $currParams = $this->request->params();
        if ($params) {
            $currParams = array_merge($currParams, $params);
        }

        $url = $this->request->path;
        if ($currParams) {
            $url .= '?' . http_build_query($currParams);
        }

        header("Location: $url", true, $status);
        die;
    }
}
