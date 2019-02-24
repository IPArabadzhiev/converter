<?php
namespace Router;

class Request implements RequestInterface
{
    function __construct()
    {
        $this->bootstrapSelf();
    }
    private function bootstrapSelf()
    {
        foreach($_SERVER as $key => $value)
        {
            $this->{$this->toCamelCase($key)} = $value;
        }
    }
    private function toCamelCase($string)
    {
        $result = strtolower($string);

        preg_match_all('/_[a-z]/', $result, $matches);
        foreach($matches[0] as $match)
        {
            $c = str_replace('_', '', strtoupper($match));
            $result = str_replace($match, $c, $result);
        }
        return $result;
    }
    public function getBody()
    {
        if ($this->requestMethod === "GET")
        {
            return $_GET;
        }
        if ($this->requestMethod == "POST")
        {
            $paramsString = file_get_contents('php://input');
            if ($paramsString === '') {
                $paramsString = '{}';
            }
            $postParams = array_merge($_POST, json_decode($paramsString, true));
            return $postParams;
        }

        return  ['a' => 10];
    }
}