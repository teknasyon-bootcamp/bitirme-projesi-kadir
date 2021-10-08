<?php

namespace Core;


use App\Controllers\HomeController;
use App\Exceptions\ForbiddenException;
use App\Exceptions\NotFoundException;
use App\Exceptions\PageExpiredException;

class Router
{
    public Request $request;
    protected array $routes = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function get($path, $callback)
    {
        $this->routes['get'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['post'][$path] = $callback;
    }

    /**
     * @throws NotFoundException
     * @throws PageExpiredException
     */
    public function resolve()
    {
        $path = $this->request->getPath();
        $method = $this->request->method();
        $callback = $this->routes[$method][$path] ?? false;

        if ($method === 'post') {
            validate_input();

            $token = Session::get('token');
            $postToken = $this->request->getBody()["_token"] ?? '';

            if ($token !== $postToken) {
                throw new PageExpiredException();
            }
        }

        if ($callback === false) {
            throw new NotFoundException();
        }

        if (is_string($callback)) {
            return $this->view($callback);
        }

        if (is_array($callback)) {
            $controller = new $callback[0]();
            Application::$app->controller = $controller;
            $controller->action = $callback[1];
            $callback[0] = $controller;

            foreach ($controller->getMiddlewares() as $middleware) {
                $middleware->execute();
            }
        }

        return call_user_func($callback, $this->request);
    }

    public function view(string $view, ?string $title = null, array $data = [])
    {
        if (is_null($title)) {
            $title = env('APP_NAME');
        }

        foreach ($data as $key => $value) {
            $$key = $value;
        }

        ob_start();
        require_once __DIR__ . "/../app/views/$view.php";
        unset($_SESSION['error']);
        return ob_get_clean();
    }
}