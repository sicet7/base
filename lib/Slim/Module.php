<?php

namespace Sicet7\Base\Slim;

use DI\Bridge\Slim\ControllerInvoker;
use Sicet7\Base\ModuleInterface;
use DI\Bridge\Slim\CallableResolver;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;
use Slim\Error\Renderers\HtmlErrorRenderer;
use Slim\Error\Renderers\JsonErrorRenderer;
use Slim\Error\Renderers\PlainTextErrorRenderer;
use Slim\Error\Renderers\XmlErrorRenderer;
use Slim\Factory\AppFactory;
use Invoker\CallableResolver as InvokerCallableResolver;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Routing\RouteCollector;
use function DI\create;
use function DI\get;

final class Module implements ModuleInterface
{

    /**
     * @return array
     * @throws \Exception
     */
    public static function getDefinitions(): array
    {
        return [
            RequestHandlerInterface::class => get(App::class),
            App::class => function (ContainerInterface $container) {
                $app = AppFactory::createFromContainer($container);
                if ($container->has(InvocationStrategyInterface::class)) {
                    $app->getRouteCollector()->setDefaultInvocationStrategy(
                        $container->get(InvocationStrategyInterface::class)
                    );
                }
                return $app;
            },
            InvokerCallableResolver::class => create(InvokerCallableResolver::class)
                ->constructor(get(ContainerInterface::class)),
            CallableResolverInterface::class => function (InvokerCallableResolver $callableResolver) {
                return new CallableResolver($callableResolver);
            },
            ControllerInvoker::class => create(ControllerInvoker::class)
                ->constructor(get(ContainerInterface::class)),
            InvocationStrategyInterface::class => get(ControllerInvoker::class),
            RouteCollector::class => create(RouteCollector::class)
                ->constructor(
                    get(ResponseFactoryInterface::class),
                    get(CallableResolverInterface::class),
                    get(ContainerInterface::class),
                    get(InvocationStrategyInterface::class)
                ),
            RouteCollectorInterface::class => get(RouteCollector::class),
            HtmlErrorRenderer::class => create(HtmlErrorRenderer::class),
            JsonErrorRenderer::class => create(JsonErrorRenderer::class),
            PlainTextErrorRenderer::class => create(PlainTextErrorRenderer::class),
            XmlErrorRenderer::class => create(XmlErrorRenderer::class),
        ];
    }
}