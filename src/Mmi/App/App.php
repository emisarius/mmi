<?php

/**
 * Mmi Framework (https://github.com/milejko/mmi.git)
 *
 * @link       https://github.com/milejko/mmi.git
 * @copyright  Copyright (c) 2010-2020 Mariusz Miłejko (mariusz@milejko.pl)
 * @license    https://en.wikipedia.org/wiki/BSD_licenses New BSD License
 */

namespace Mmi\App;

use Mmi\EventManager\EventManager;
use Mmi\Http\Request;
use Mmi\Http\Response;
use Mmi\Mvc\ActionHelper;
use Mmi\Mvc\RouterApply;

/**
 * Application class
 */
class App extends AppAbstract
{
    const PROFILER_PREFIX                    = 'Mmi\App: ';
    const APPLICATION_COMPILE_PATH           = BASE_PATH . '/var/compile';

    /**
     * Sets error and exception handler
     */
    protected function setErrorHandler(): self
    {
        //exception handler
        set_exception_handler([$this->container->get(AppErrorHandler::class), 'exceptionHandler']);
        //error handler
        set_error_handler([$this->container->get(AppErrorHandler::class), 'errorHandler']);
        $this->profiler->event(self::PROFILER_PREFIX . 'error handler setup');
        return $this;
    }

    /**
     * Application run
     */
    public function run(): void
    {
        $request = $this->container->get(Request::class);
        $interceptor = $this->container->has(AppEventInterceptorInterface::class) ? $this->container->get(AppEventInterceptorInterface::class) : null;
        //intercept before dispatch
        if (null !== $interceptor) {
            $interceptor->init();
            $this->profiler->event(self::PROFILER_PREFIX . 'interceptor init()');
            $interceptor->beforeDispatch();
            $this->profiler->event(self::PROFILER_PREFIX . 'interceptor beforeDispatch()');
        }

        /** @var EventManager $eventManager */
        $eventManager = $this->container->get(EventManager::class);

        //render content
        $content = $this->container->get(ActionHelper::class)->forward($request);
        //intercept before send
        if (null !== $interceptor) {
            $interceptor->beforeSend();
            $this->profiler->event(self::PROFILER_PREFIX . 'interceptor beforeSend()');
        }
        //set content to response
        $this->container->get(Response::class)
            ->setContent($content);

        $this->sendResponse();
    }

    /**
     * Experimental. May be subject to change
     */
    public function handleRequest(Request $request): Response
    {
        ($this->container->get(RouterApply::class))($request);

        $interceptor = $this->container->has(AppEventInterceptorInterface::class) ? $this->container->get(AppEventInterceptorInterface::class) : null;
        //intercept before dispatch
        if (null !== $interceptor) {
            $interceptor->init();
            $this->profiler->event(self::PROFILER_PREFIX . 'interceptor init()');
            $interceptor->beforeDispatch();
            $this->profiler->event(self::PROFILER_PREFIX . 'interceptor beforeDispatch()');
        }
        //render content
        $content = $this->container->get(ActionHelper::class)->forward($request);
        //intercept before send
        if (null !== $interceptor) {
            $interceptor->beforeSend();
            $this->profiler->event(self::PROFILER_PREFIX . 'interceptor beforeSend()');
        }
        //set content to response
        $this->container->get(Response::class)
            ->setContent($content);

        return $this->container->get(Response::class);
    }

    public function sendResponse(): void
    {
        $this->profiler->event(self::PROFILER_PREFIX . 'send response to the client');
        $eventManager->trigger(AppMvcEvents::EVENT_FINISH, $this, []);
        $this->container->get(Response::class)->send();
    }
}