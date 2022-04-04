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
        $jsonParams = $this->json() ?: [];

        return array_merge($queryParams, $bodyParams, $jsonParams);
    }

    public function form()
    {
        return $this->innerRequest->getParsedBody();
    }

    public function json()
    {
        return json_decode($this->contents(), true);
    }

    public function contents()
    {
        return $this->innerRequest->getBody()->getContents();
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->innerRequest, $method)) {
            return call_user_func_array([$this->innerRequest, $method], $arguments);
        }

        throw new \Exception('method not exists: ' . $method);
    }
}
