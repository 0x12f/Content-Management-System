<?php

use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

// doctrine
$container[\Doctrine\ORM\EntityManager::class] = function (ContainerInterface $c) : EntityManager {
    $settings = $c->get('doctrine');

    foreach ($settings['types'] as $type => $class) {
        \Doctrine\DBAL\Types\Type::addType($type, $class);
    }

    $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
        $settings['meta']['entity_path'],
        $settings['meta']['auto_generate_proxies'],
        $settings['meta']['proxy_dir'],
        $settings['meta']['cache'],
        false
    );

    $doctrine = \Doctrine\ORM\EntityManager::create($settings['connection'], $config);

//    $doctrineConnection = $doctrine->getConnection();
//    $stack = new \Doctrine\DBAL\Logging\DebugStack();
//    $doctrineConnection->getConfiguration()->setSQLLogger($stack);
//
//    register_shutdown_function(function () use ($doctrine) {
//        $time   = round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 7);
//        $memory = str_convert_size(memory_get_usage());
//
//        /** @var \Doctrine\ORM\EntityManager $em */
//        $dbStack = $doctrine->getConfiguration()->getSQLLogger();
//        $dbQueries = count($dbStack->queries);
//        $dbTime = 0;
//
//        foreach ($dbStack->queries as $query) {
//            $dbTime += $query['executionMS'];
//        }
//
//        pre('X-Memory', $memory);
//        pre('X-Time', $time . ' ms');
//        pre('X-DB-Queries', $dbQueries);
//        pre('X-DB-Execution', round($dbTime, 7) . ' ms');
//        exit;
//    });

    return $doctrine;
};

// view twig file render
$container['view'] = function (ContainerInterface $c) {
    $settings = array_merge(
        $c->get('renderer'),
        $c->get('twig'),
        [
            'displayErrorDetails' => $c->get('settings')['displayErrorDetails'],
        ]
    );

    $view = new \Slim\Views\Twig($settings['template_path'], [
        'debug' => $settings['displayErrorDetails'],
    ]);

    $view['_request'] = $_REQUEST;
    $view['styles'] = new ArrayObject();
    $view['scripts'] = new ArrayObject();

    $view->addExtension(
        new \Slim\Views\TwigExtension(
            $c->get('router'),
            \Slim\Http\Uri::createFromEnvironment(new \Slim\Http\Environment($_SERVER))
        )
    );
    $view->addExtension(new \Application\TwigExtension());

    $view->addExtension(new \Twig\Extra\Intl\IntlExtension());
    $view->addExtension(new \Twig_Extensions_Extension_Text());
    $view->addExtension(new \Twig\Extension\StringLoaderExtension());
    $view->addExtension(new \Phive\Twig\Extensions\Deferred\DeferredExtension());

    // if debug
    if ($settings['displayErrorDetails']) {
        $view->addExtension(new \Twig\Extension\ProfilerExtension($c['twig_profile']));
        $view->addExtension(new \Twig\Extension\DebugExtension());
    }

    // set cache path
    if (!$settings['displayErrorDetails']) {
        $env = $view->getEnvironment();
        $env->setCache($settings['caches_path']);
    }

    return $view;
};

// twig profile
$container['twig_profile'] = function (ContainerInterface $c) {
    return new \Twig\Profiler\Profile();
};

// monolog
$container['monolog'] = function (ContainerInterface $c) {
    $settings = $c->get('logger');

    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));

    return $logger;
};

// not found
$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c->get('view')->render($response, 'p404.twig')->withStatus(404);
    };
};

// not allowed
$container['notAllowedHandler'] = function ($c) {
    return function ($request, $response, $methods) use ($c) {
        return $c->get('view')->render($response, 'p405.twig', ['methods' => $methods])->withStatus(401);
    };
};

// error
$container['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        return $c->get('view')->render($response, 'p500.twig', ['exception' => $exception])->withStatus(500)
        ;
    };
};
