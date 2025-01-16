<?php
declare(strict_types=1);
namespace Framework;

use Framework\Exceptions\PageNotFoundException;
use ReflectionMethod;
use UnexpectedValueException;
use Framework\Request;
use Framework\Container;

class Dispatcher
{
    public function __construct(private Router $router, private Container $container, private array $middleware_classes)
    {
        
    }

    public function handle(Request $request): Response
    {
        $path = $this->getPath($request->uri);

        $params = $this->router->match($path, $request->form);
        if($params === false){
            throw new PageNotFoundException("No route matched for '{$path}' with URL request method {$request->form}");
        }
        $controllers = $this->getControllerName($params);
        $method = $this->getMethodName($params);
        $controller = $this->container->get($controllers);
        $controller->setViewer($this->container->get(TemplateViewerInterface::class));
        $controller->setResponse($this->container->get(Response::class));

        $args = $this->getMethodArguments($controllers, $method, $params);
        $controller_handler = new ControllerRequestHandler($controller, $method, $args);
        $middleware = $this->getMiddleware($params);
       
        $middleware_handler = new MiddlewareRequestHandler($middleware, $controller_handler);
        return $middleware_handler->handle($request);
    }

    public function getMethodArguments(string $controller, string $method, array $params) : array
    {
        $args = [];
        $method = new ReflectionMethod($controller, $method);
        foreach($method->getParameters() as $parameter){
            $name = $parameter->getName();
            $args[$name] = $params[$name];
        }
        return $args;
    }

    private function getControllerName(array $params): string
    {
        $controller = strtolower($params['controller']);
        $controller = ucwords($controller, "-");
        $controller = str_replace("-", "", $controller);
        $namespace = "App\Controllers";

        if(array_key_exists("namespace", $params)){
            $namespace .= "\\".$params["namespace"];
        }
        return "$namespace\\$controller";
    }

    private function getMethodName(array $params): string
    {
        $method = strtolower($params['method']);
        $method = ucwords($method, "-");
        $method = str_replace("-", "", $method);
        return  lcfirst($method);
    }

    private function getPath(string $uri): string
    {
        $site = $_ENV["SITE_DIR"];
        $path = substr(parse_url($uri, PHP_URL_PATH), strlen($site));
        if($path === false){
            throw new UnexpectedValueException("Malformed URL: '{$uri}'");
        }
        return $path;
    }

    private function getMiddleware(array $params): array
    {
        if(!array_key_exists("middleware", $params)){
            return [];
        }
        $middleware = explode("|", $params["middleware"]);
       
        array_walk($middleware, function(&$value){
            if(!array_key_exists($value, $this->middleware_classes)){
                throw new UnexpectedValueException("Middleware '$value' not found in config settings");
            }
            $value = $this->container->get($this->middleware_classes[$value]);
        });
        return $middleware;
    }
}