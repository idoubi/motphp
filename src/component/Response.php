<?php

namespace mot\component;

class Response
{

    protected $response;

    public function __construct($response)
    {
        $this->innerResponse = $response;
    }

    public function html()
    {
    }

    public function error($code, $msg)
    {
    }

    public function stdout($code, $msg = '', $data = null)
    {
        $json = [
            'code' => $code,
            'message' => $msg,
        ];
        if ($data !== null) {
            $json['data'] = $data;
        }

        return $this->json($json);
    }

    public function json($data)
    {
        header("Content-Type: application/json", true, 200);
        echo json_encode($data);
        die;
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this->innerResponse, $method)) {
            return call_user_func_array([$this->innerResponse, $method], $arguments);
        }

        throw new \Exception('method not exists: ' . $method);
    }
}
