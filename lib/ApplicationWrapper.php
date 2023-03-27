<?php

namespace Sicet7\Base;

use Composer\InstalledVersions;
use DI\Bridge\Slim\CallableResolver;
use DI\Bridge\Slim\ControllerInvoker;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Configuration as MigrationConfiguration;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\CurrentCommand;
use Doctrine\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\Migrations\Tools\Console\Command\DumpSchemaCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\ListCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\RollupCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Doctrine\ORM\Configuration as ORMConfiguration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Invoker\CallableResolver as InvokerCallableResolver;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\ResolverChain;
use Monolog\Logger;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Sicet7\Base\Factories\AutowireFactory;
use Sicet7\Base\Factories\JsonResponseFactory;
use Sicet7\Base\Server\HttpWorker;
use Sicet7\Base\Server\WorkerParams;
use Slim\App;
use Slim\Error\Renderers\HtmlErrorRenderer;
use Slim\Error\Renderers\JsonErrorRenderer;
use Slim\Error\Renderers\PlainTextErrorRenderer;
use Slim\Error\Renderers\XmlErrorRenderer;
use Slim\Factory\AppFactory;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Routing\RouteCollector;
use Spiral\Goridge\Relay;
use Spiral\Goridge\RelayInterface;
use Spiral\Goridge\RPC\RPC;
use Spiral\Goridge\RPC\RPCInterface;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\EnvironmentInterface;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Http\PSR7WorkerInterface;
use Spiral\RoadRunner\Worker as RoadRunnerWorker;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory as SymfonyToPsrHttpFactory;
use function DI\add;
use function DI\create;
use function DI\get;

abstract class ApplicationWrapper
{
    protected const CONTAINER_INSTANCE = Container::class;

    /**
     * @var ContainerInterface
     */
    protected readonly ContainerInterface $container;

    /**
     * @throws \Exception
     */
    final public function __construct()
    {
        $builder = new ContainerBuilder(self::CONTAINER_INSTANCE);
        $builder->addDefinitions([
            //Application
            ApplicationWrapper::class => $this,

            //Database
            EventManager::class => create(EventManager::class),
            EntityManager::class => create(EntityManager::class)
                ->constructor(
                    get(Connection::class),
                    get(ORMConfiguration::class),
                    get(EventManager::class)
                ),
            EntityManagerInterface::class => get(EntityManager::class),
            ExistingConfiguration::class => create(ExistingConfiguration::class)
                ->constructor(get(MigrationConfiguration::class)),
            ExistingConnection::class => create(ExistingConnection::class)
                ->constructor(get(Connection::class)),
            DependencyFactory::class => function(
                ExistingConfiguration $configuration,
                ExistingConnection $connection,
                ContainerInterface $container
            ): DependencyFactory {
                $factory = DependencyFactory::fromConnection(
                    $configuration,
                    $connection,
                    $container->has(LoggerInterface::class) ? $container->get(LoggerInterface::class) : null
                );
                $factory->freeze();
                return $factory;
            },
            Migrator::class => create(Migrator::class)
                ->constructor(
                    'Doctrine Migrations',
                    InstalledVersions::getVersion('doctrine/migrations')
                )->method('addCommands', get('migrator.commands')),

            CurrentCommand::class => create(CurrentCommand::class)
                ->constructor(get(DependencyFactory::class)),
            DumpSchemaCommand::class => create(DumpSchemaCommand::class)
                ->constructor(get(DependencyFactory::class)),
            ExecuteCommand::class => create(ExecuteCommand::class)
                ->constructor(get(DependencyFactory::class)),
            GenerateCommand::class => create(GenerateCommand::class)
                ->constructor(get(DependencyFactory::class)),
            LatestCommand::class => create(LatestCommand::class)
                ->constructor(get(DependencyFactory::class)),
            MigrateCommand::class => create(MigrateCommand::class)
                ->constructor(get(DependencyFactory::class)),
            RollupCommand::class => create(RollupCommand::class)
                ->constructor(get(DependencyFactory::class)),
            StatusCommand::class => create(StatusCommand::class)
                ->constructor(get(DependencyFactory::class)),
            VersionCommand::class => create(VersionCommand::class)
                ->constructor(get(DependencyFactory::class)),
            UpToDateCommand::class => create(UpToDateCommand::class)
                ->constructor(get(DependencyFactory::class)),
            SyncMetadataCommand::class => create(SyncMetadataCommand::class)
                ->constructor(get(DependencyFactory::class)),
            ListCommand::class => create(ListCommand::class)
                ->constructor(get(DependencyFactory::class)),
            DiffCommand::class => create(DiffCommand::class)
                ->constructor(get(DependencyFactory::class)),

            'migrator.commands' => add([
                get(CurrentCommand::class),
                get(DiffCommand::class),
                get(DumpSchemaCommand::class),
                get(ExecuteCommand::class),
                get(GenerateCommand::class),
                get(LatestCommand::class),
                get(ListCommand::class),
                get(MigrateCommand::class),
                get(RollupCommand::class),
                get(StatusCommand::class),
                get(SyncMetadataCommand::class),
                get(UpToDateCommand::class),
                get(VersionCommand::class),
            ]),

            //Logging
            Logger::class => create(Logger::class)
                ->constructor('main'),
            LoggerInterface::class => get(Logger::class),

            //PSR-7
            Psr17Factory::class => create(Psr17Factory::class),
            RequestFactoryInterface::class => get(Psr17Factory::class),
            ResponseFactoryInterface::class => get(Psr17Factory::class),
            ServerRequestFactoryInterface::class => get(Psr17Factory::class),
            StreamFactoryInterface::class => get(Psr17Factory::class),
            UploadedFileFactoryInterface::class => get(Psr17Factory::class),
            UriFactoryInterface::class => get(Psr17Factory::class),

            //Slim
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
            AttributeRouteCollector::class => create(AttributeRouteCollector::class)
                ->constructor(
                    get(ResponseFactoryInterface::class),
                    get(CallableResolverInterface::class),
                    get(ContainerInterface::class),
                    get(InvocationStrategyInterface::class)
                ),
            RouteCollector::class => get(AttributeRouteCollector::class),
            RouteCollectorInterface::class => get(AttributeRouteCollector::class),
            HtmlErrorRenderer::class => create(HtmlErrorRenderer::class),
            JsonErrorRenderer::class => create(JsonErrorRenderer::class),
            PlainTextErrorRenderer::class => create(PlainTextErrorRenderer::class),
            XmlErrorRenderer::class => create(XmlErrorRenderer::class),
            SymfonyToPsrHttpFactory::class => create(SymfonyToPsrHttpFactory::class)
                ->constructor(
                    get(ServerRequestFactoryInterface::class),
                    get(StreamFactoryInterface::class),
                    get(UploadedFileFactoryInterface::class),
                    get(ResponseFactoryInterface::class),
                ),
            JsonResponseFactory::class => create(JsonResponseFactory::class)
                ->constructor(get(SymfonyToPsrHttpFactory::class)),
            AutowireFactory::class => function(ContainerInterface $container): AutowireFactory {
                return new AutowireFactory(new ResolverChain([
                    0 => new AssociativeArrayResolver(),
                    1 => new TypeHintContainerResolver($container),
                    2 => new DefaultValueResolver(),
                ]));
            },

            //Server
            WorkerParams::class => create(WorkerParams::class),
            Environment::class => function (): Environment
            {
                return Environment::fromGlobals();
            },
            EnvironmentInterface::class => get(Environment::class),
            RelayInterface::class => function (
                EnvironmentInterface $environment
            ): RelayInterface {
                return Relay::create($environment->getRelayAddress());
            },
            RPCInterface::class => function (
                EnvironmentInterface $environment
            ): RPCInterface {
                return RPC::create($environment->getRPCAddress());
            },
            RoadRunnerWorker::class => function (
                RelayInterface $relay,
                WorkerParams $workerParams
            ): RoadRunnerWorker {
                return new RoadRunnerWorker($relay, $workerParams->interceptSideEffects);
            },
            PSR7Worker::class => create(PSR7Worker::class)
                ->constructor(
                    get(RoadRunnerWorker::class),
                    get(ServerRequestFactoryInterface::class),
                    get(StreamFactoryInterface::class),
                    get(UploadedFileFactoryInterface::class),
                ),
            PSR7WorkerInterface::class => get(PSR7Worker::class),
            HttpWorker::class => function(
                RequestHandlerInterface $requestHandler,
                PSR7WorkerInterface $PSR7Worker,
                ResponseFactoryInterface $responseFactory,
                ContainerInterface $container
            ): HttpWorker {
                $logger = null;
                $eventDispatcher = null;
                if ($container->has(LoggerInterface::class)) {
                    $logger = $container->get(LoggerInterface::class);
                }
                if ($container->has(EventDispatcherInterface::class)) {
                    $eventDispatcher = $container->get(EventDispatcherInterface::class);
                }
                return new HttpWorker(
                    $requestHandler,
                    $PSR7Worker,
                    $responseFactory,
                    $logger,
                    $eventDispatcher
                );
            },
        ]);
        $builder->addDefinitions($this->definitions());
        $builder->useAttributes(false);
        $builder->useAutowiring(false);
        $this->container = $builder->build();
    }

    abstract protected function definitions(): array;
}