<?php

namespace mot\middleware;

use Illuminate\Pagination\Paginator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class Paginate
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        Paginator::currentPathResolver(function () use ($request) {
            return $request->getUri()->getPath();
        });
        Paginator::currentPageResolver(function ($pageName = 'page') use ($request) {
            return $request->getQueryParams()[$pageName] ?? 1;
        });

        return $handler->handle($request);
    }
}
