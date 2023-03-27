<?php

namespace Tests;

use DI\Bridge\Slim\ControllerInvoker;
use DI\ContainerBuilder;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\Migrations\Configuration\Configuration;
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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Invoker\CallableResolver as InvokerCallableResolver;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Sicet7\Base\ApplicationWrapper;
use Sicet7\Base\AttributeRouteCollector;
use Sicet7\Base\Factories\AutowireFactory;
use Sicet7\Base\Factories\JsonResponseFactory;
use Sicet7\Base\Migrator;
use Sicet7\Base\Server\HttpWorker;
use Sicet7\Base\Server\WorkerParams;
use Slim\App;
use Slim\Error\Renderers\HtmlErrorRenderer;
use Slim\Error\Renderers\JsonErrorRenderer;
use Slim\Error\Renderers\PlainTextErrorRenderer;
use Slim\Error\Renderers\XmlErrorRenderer;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Routing\RouteCollector;
use Spiral\Goridge\RelayInterface;
use Spiral\Goridge\RPC\RPCInterface;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\EnvironmentInterface;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Http\PSR7WorkerInterface;
use Spiral\RoadRunner\Worker as RoadRunnerWorker;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory as SymfonyToPsrHttpFactory;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use function DI\decorate;

final class ApplicationWrapperContainerTest extends TestCase
{
    private static ?ApplicationWrapper $application = null;

    /**
     * @return void
     * @throws \Exception
     */
    public static function setUpBeforeClass(): void
    {
        self::$application = new class() extends ApplicationWrapper {
            /**
             * @param ContainerBuilder $builder
             * @return void
             */
            protected function applyDefinitions(ContainerBuilder $builder): void
            {
                $builder->addDefinitions([
                    Connection::class => function () {
                        return DriverManager::getConnection((new DsnParser())->parse('sqlite3:///:memory:'));
                    },
                    Configuration::class => function () {
                        return new Configuration();
                    },
                    \Doctrine\ORM\Configuration::class => function (): \Doctrine\ORM\Configuration {
                        return ORMSetup::createAttributeMetadataConfiguration([
                            __DIR__
                        ], true);
                    },
                    WorkerParams::class => decorate(function (WorkerParams $workerParams): WorkerParams {
                        $workerParams->interceptSideEffects = false;
                        return $workerParams;
                    }),
                ]);
            }

            /**
             * @return ContainerInterface
             */
            public function getContainer(): ContainerInterface
            {
                return $this->container;
            }
        };
    }

    public static function tearDownAfterClass(): void
    {
        self::$application = null;
    }

    #[Test]
    #[TestWith([EventManager::class])]
    #[TestWith([EntityManager::class])]
    #[TestWith([EntityManagerInterface::class])]
    #[TestWith([ExistingConfiguration::class])]
    #[TestWith([ExistingConnection::class])]
    #[TestWith([DependencyFactory::class])]
    #[TestWith([Migrator::class])]
    #[TestWith([CurrentCommand::class])]
    #[TestWith([DiffCommand::class])]
    #[TestWith([DumpSchemaCommand::class])]
    #[TestWith([ExecuteCommand::class])]
    #[TestWith([GenerateCommand::class])]
    #[TestWith([LatestCommand::class])]
    #[TestWith([ListCommand::class])]
    #[TestWith([MigrateCommand::class])]
    #[TestWith([RollupCommand::class])]
    #[TestWith([StatusCommand::class])]
    #[TestWith([SyncMetadataCommand::class])]
    #[TestWith([UpToDateCommand::class])]
    #[TestWith([VersionCommand::class])]
    #[TestWith([RequestHandlerInterface::class])]
    #[TestWith([App::class])]
    #[TestWith([InvokerCallableResolver::class])]
    #[TestWith([CallableResolverInterface::class])]
    #[TestWith([ControllerInvoker::class])]
    #[TestWith([InvocationStrategyInterface::class])]
    #[TestWith([RouteCollector::class])]
    #[TestWith([RouteCollectorInterface::class])]
    #[TestWith([HtmlErrorRenderer::class])]
    #[TestWith([JsonErrorRenderer::class])]
    #[TestWith([PlainTextErrorRenderer::class])]
    #[TestWith([XmlErrorRenderer::class])]
    #[TestWith([SymfonyToPsrHttpFactory::class])]
    #[TestWith([JsonResponseFactory::class])]
    #[TestWith([AttributeRouteCollector::class])]
    #[TestWith([AutowireFactory::class])]
    #[TestWith([Logger::class])]
    #[TestWith([LoggerInterface::class])]
    #[TestWith([Psr17Factory::class])]
    #[TestWith([RequestFactoryInterface::class])]
    #[TestWith([ResponseFactoryInterface::class])]
    #[TestWith([ServerRequestFactoryInterface::class])]
    #[TestWith([StreamFactoryInterface::class])]
    #[TestWith([UploadedFileFactoryInterface::class])]
    #[TestWith([UriFactoryInterface::class])]
    #[TestWith([WorkerParams::class])]
    #[TestWith([Environment::class])]
    #[TestWith([EnvironmentInterface::class])]
    #[TestWith([RelayInterface::class])]
    #[TestWith([RPCInterface::class])]
    #[TestWith([RoadRunnerWorker::class])]
    #[TestWith([PSR7Worker::class])]
    #[TestWith([PSR7WorkerInterface::class])]
    #[TestWith([HttpWorker::class])]
    public function canGetRegistration(string $class): void
    {
        $this->assertInstanceOf($class, self::$application->getContainer()->get($class));
    }

    #[Test]
    #[Depends('canGetRegistration')]
    public function canRunMigration(): void
    {
        $input = new StringInput('migrations:migrate --allow-no-migration');
        $input->setInteractive(false);
        $code = self::$application->getContainer()->get(MigrateCommand::class)->run($input, new NullOutput());
        $this->assertEquals(0, $code);
    }

    #[Test]
    #[Depends('canGetRegistration')]
    public function canLogToMonolog(): void
    {
        $logger = self::$application->getContainer()->get(Logger::class);

        $logger = $logger->withName('test');

        $handlerList = [];

        foreach (Level::VALUES as $value) {
            $handlerList[$value] = new TestHandler($value);
            $logger->pushHandler($handlerList[$value]);
        }

        $msgPrefix = 'hello with level ';

        foreach (Level::VALUES as $value) {
            $logger->log($value, $msgPrefix . Level::fromValue($value)->name);
        }


        foreach ($handlerList as $handlerLevel => $handler) {

            foreach (Level::VALUES as $value) {
                $level = Level::fromValue($value);
                if ($value < $handlerLevel) {
                    $this->assertFalse($handler->hasRecordThatContains($msgPrefix . $level->name, $level));
                } else {
                    $this->assertTrue($handler->hasRecordThatContains($msgPrefix . $level->name, $level));
                }
            }

        }
    }
}