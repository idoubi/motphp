<?php

namespace mot\component;

class Request
{
    protected $innerRequest;

    public function __construct($request)
    {
        $this->innerRequest = $request;
    }

    public function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    public function getQueryParam($key, $default = null)
    {
        return $this->param($key, $default);
    }

    public function param($key, $default = null, $filter = '')
    {
        $params = $this->params();

        if (!empty($params[$key])) {
            if (is_callable($filter)) {
                return $filter($params[$key]);
            }

            return $params[$key];
        }

        if ($default) {
            return $default;
        }

        return null;
    }

    public function params()
    {
        $queryParams = $this->innerRequest->getQueryParams() ?: [];
        $bodyParams = $this->innerRequest->getParsedBody() ?: [];

        return array_merge($queryParams, $bodyParams);
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->innerRequest, $method)) {
            return $this->innerRequest->$method($arguments);
        }

        throw new \Exception('method not exists: ' . $method);
    }
}
