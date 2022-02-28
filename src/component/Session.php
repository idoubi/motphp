<?php

namespace mot\component;

class Session
{
    public function __construct($session)
    {
        $this->innerSession = $session;
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->innerSession, $method)) {
            return call_user_func_array([$this->innerSession, $method], $arguments);
        }

        throw new \Exception('method not exists: ' . $method);
    }
}
