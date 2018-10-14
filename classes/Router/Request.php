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
        if($this->requestMethod === "GET")
        {
            return ['a' => 5];
        }
        if ($this->requestMethod == "POST")
        {
            $postParams = array_merge($_POST, json_decode(file_get_contents('php://input'), true));
            return $postParams;
        }

        return  ['a' => 10];
    }
}