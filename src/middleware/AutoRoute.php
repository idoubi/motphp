<?php

namespace mot\middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Slim\Exception\HttpNotFoundException;
use Slim\Views\Twig;
use Symfony\Component\HttpFoundation\Session\Session;
use mot\controller\Handler;
use mot\component\Request as MotRequest;
use mot\component\Response as MotResponse;
use mot\component\View as MotView;
use mot\component\Session as MotSession;

class AutoRoute
{
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        try {
            $response = $handler->handle($request);

            return $response;
        } catch (\Exception $e) {
            if ($e instanceof HttpNotFoundException) {
                $uri = $request->getUri();
                $path = $uri->getPath();

                $response = new Response();

                $this->container->set('request', function () use ($request) {
                    return new MotRequest($request);
                });
                $this->container->set('response', function () use ($response) {
                    return new MotResponse($response);
                });
                $view = $this->container->get(Twig::class);
                $this->container->set('view', function () use ($view) {
                    return new MotView($view);
                });

                $session = $this->container->get(Session::class);
                $this->container->set('session', function () use ($session) {
                    return new MotSession($session);
                });

                $handler = new Handler($path, $this->container);

                return $handler->handle($request, $response, []);
            }
        }
    }
}
