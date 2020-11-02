<?php

use DI\Definition\Exception\InvalidDefinition;
use Mmi\App\AppProfilerInterface;
use Mmi\Cache\PrivateCache;
use Mmi\Http\HttpServerEnv;
use Mmi\Http\Request;
use Mmi\Mvc\ActionHelper;
use Mmi\Mvc\EmptyRouterConfig;
use Mmi\Mvc\Messenger;
use Mmi\Mvc\Router;
use Mmi\Mvc\RouterConfig;
use Mmi\Mvc\RouterConfigAbstract;
use Mmi\Mvc\View;
use Mmi\Mvc\ViewHelper\HelperAbstract;
use Mmi\Translate;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use function DI\autowire;
use function DI\get;

return [
    ActionHelper::class => autowire(ActionHelper::class),

    Messenger::class => autowire(Messenger::class),

    Router::class => function (ContainerInterface $container) {
        //missing router config
        if (!$container->has(RouterConfigAbstract::class)) {
            $container->get(LoggerInterface::class)->warning('Router config implementing ' . RouterConfigAbstract::class . ' cannot be injected. To fix this, add definition of ' . RouterConfigAbstract::class . ' with suitable target object in your application\'s DI configuration.');
            $container->get(AppProfilerInterface::class)->event('Mmi\Mvc: router configuration missing');
            return new Router(new EmptyRouterConfig());
        }
        return new Router($container->get(RouterConfigAbstract::class));
    },

    View::class => autowire(View::class)
        ->method('setCdn', get('app.view.cdn'))
        ->method('setRequest', get(Request::class)),
    
    /*function (ContainerInterface $container) {
        return (new View($container->get(Translate::class), $container->get(PrivateCache::class)))
            //ustawienie cdn
            ->setCdn($container->get('app.view.cdn'))
            //ustawienie requestu
            ->setRequest($container->get(Request::class))
            //ustawianie baseUrl
            ->setBaseUrl($container->get(HttpServerEnv::class)->baseUrl);
    },*/
    HelperAbstract::class => autowire(HelperAbstract::class),
];
