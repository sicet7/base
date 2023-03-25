<?php

namespace Tests;

use DI\Bridge\Slim\ControllerInvoker;
use DI\ContainerBuilder;
use Invoker\CallableResolver as InvokerCallableResolver;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Server\RequestHandlerInterface;
use Sicet7\Base\Psr17\Module as Psr17Module;
use Sicet7\Base\Slim\Module as SlimModule;
use Slim\App;
use Slim\Error\Renderers\HtmlErrorRenderer;
use Slim\Error\Renderers\JsonErrorRenderer;
use Slim\Error\Renderers\PlainTextErrorRenderer;
use Slim\Error\Renderers\XmlErrorRenderer;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Routing\RouteCollector;

final class SlimModuleTest extends ContainerTestCase
{
    /**
     * @param ContainerBuilder $builder
     * @return void
     * @throws \Exception
     */
    public static function addDefinitions(ContainerBuilder $builder): void
    {
        $builder->addDefinitions(
            Psr17Module::getDefinitions(),
            SlimModule::getDefinitions()
        );
    }

    #[Test]
    public function canGetAllRegistrations(): void
    {
        $this->checkRegistrations([
            RequestHandlerInterface::class,
            App::class,
            InvokerCallableResolver::class,
            CallableResolverInterface::class,
            ControllerInvoker::class,
            InvocationStrategyInterface::class,
            RouteCollector::class,
            RouteCollectorInterface::class,
            HtmlErrorRenderer::class,
            JsonErrorRenderer::class,
            PlainTextErrorRenderer::class,
            XmlErrorRenderer::class,
        ]);
    }
}