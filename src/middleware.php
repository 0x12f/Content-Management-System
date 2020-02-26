<?php

use Slim\Http\Request;
use Slim\Http\Response;

// RunTracy
$app->add(new RunTracy\Middlewares\TracyMiddleware($app));

// http cache
$app->add(new \Slim\HttpCache\Cache('public', 86400));

// check user auth
$app->add(\App\Application\Middlewares\AuthorizationMiddleware::class);

// redirect to address without slash in end
$app->add(function (Request $request, Response $response, $next) {
    $path = $request->getUri()->getPath();

    if ($path != '/' && str_ends_with('/', $path)) {
        return $response->withAddedHeader('Location', rtrim($path, '/'))->withStatus(308);
    }

    return $next($request, $response);
});

// set timezone parameter
$app->add(function (Request $request, Response $response, $next) {
    // todo add df twig function
    //date_default_timezone_set($this->get('parameter')->get('common_timezone', 'Europe/Kiev'));

    return $next($request, $response);
});
