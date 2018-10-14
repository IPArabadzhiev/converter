<?php
namespace Router;

class Router
{
    private $request;
    private $supportedHttpMethods = array(
        "GET",
        "POST"
    );
    function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }
    function __call($name, $args)
    {
        list($route, $exec) = $args;
        if(!in_array(strtoupper($name), $this->supportedHttpMethods))
        {
            $this->invalidMethodHandler();
        }
        $this->{strtolower($name)}[$this->formatRoute($route)] = $exec;
    }

    /**
     * Removes trailing forward slashes from the right of the route.
     * @param route (string)
     * @return string
     */
    private function formatRoute($route)
    {
        $result = rtrim($route, '/');
        if ($result === '')
        {
            return '/';
        }
        return $result;
    }
    private function invalidMethodHandler()
    {
        header("{$this->request->serverProtocol} 405 Method Not Allowed");
    }
    private function defaultRequestHandler()
    {
        header("{$this->request->serverProtocol} 404 Not Found");
    }
    /**
     * Resolves a route
     */
    function resolve()
    {
        $methodDictionary = $this->{strtolower($this->request->requestMethod)};
        $formattedRoute = $this->formatRoute($this->request->requestUri);
        $exec = $methodDictionary[$formattedRoute];
        if(is_null($exec))
        {
            $this->defaultRequestHandler();
            return;
        }
        list($controller, $method) = explode('@', $exec);
        $controller = "Controllers\\$controller";
        try {
            if (class_exists($controller, true)) {
                $controllerObject = new $controller();

                if (method_exists($controllerObject, $method)) {
                    $result = $controllerObject->$method((array) $this->request->getBody());
                    if (! empty($result)) {
                        echo json_encode($result);
                    }
                } else {
                    echo "Undefined method $method in $controller";
                }
            } else {
                echo "Undefined controller $controller";
            }
        } catch(\Exception $e) {
            echo "Unable to find controller $controller or method $method";
        }
    }
    function __destruct()
    {
        $this->resolve();
    }
}