<?php

namespace Tests;

use DI\Bridge\Slim\ControllerInvoker;
use DI\ContainerBuilder;
use Invoker\CallableResolver as InvokerCallableResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DependsExternal;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Server\RequestHandlerInterface;
use Sicet7\Base\Http\Collectors\AttributeRouteCollector;
use Sicet7\Base\Http\Factories\JsonResponseFactory;
use Sicet7\Base\Psr17\Module as Psr17Module;
use Sicet7\Base\Http\Module as SlimModule;
use Slim\App;
use Slim\Error\Renderers\HtmlErrorRenderer;
use Slim\Error\Renderers\JsonErrorRenderer;
use Slim\Error\Renderers\PlainTextErrorRenderer;
use Slim\Error\Renderers\XmlErrorRenderer;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Routing\RouteCollector;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory as SymfonyToPsrHttpFactory;

#[CoversClass(SlimModule::class)]
final class HttpModuleTest extends ContainerTestCase
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
    #[DependsExternal(Psr17ModuleTest::class, 'canGetAllRegistrations')]
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
            SymfonyToPsrHttpFactory::class,
            JsonResponseFactory::class,
            AttributeRouteCollector::class,
        ]);
    }
}