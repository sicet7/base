<?php

namespace Tests;

use DI\ContainerBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Sicet7\Base\Psr17\Module as Psr17Module;
use Sicet7\Base\Server\HttpWorker;
use Sicet7\Base\Server\Module as ServerModule;
use Sicet7\Base\Server\WorkerParams;
use Sicet7\Base\Slim\Module as SlimModule;
use Spiral\Goridge\RelayInterface;
use Spiral\Goridge\RPC\RPCInterface;
use Spiral\RoadRunner\Environment;
use Spiral\RoadRunner\EnvironmentInterface;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Http\PSR7WorkerInterface;
use Spiral\RoadRunner\Worker as RoadRunnerWorker;
use function DI\decorate;

#[CoversClass(ServerModule::class)]
final class ServerModuleTest extends ContainerTestCase
{
    /**
     * @param ContainerBuilder $builder
     * @return void
     * @throws \Exception
     */
    public static function addDefinitions(ContainerBuilder $builder): void
    {
        $builder->addDefinitions(
            ServerModule::getDefinitions(),
            Psr17Module::getDefinitions(),
            SlimModule::getDefinitions(),
            [
                WorkerParams::class => decorate(function (WorkerParams $workerParams): WorkerParams {
                    $workerParams->interceptSideEffects = false;
                    return $workerParams;
                }),
            ]
        );
    }

    #[Test]
    public function canGetAllRegistrations(): void
    {
        $this->checkRegistrations([
            WorkerParams::class,
            Environment::class,
            EnvironmentInterface::class,
            RelayInterface::class,
            RPCInterface::class,
            RoadRunnerWorker::class,
            PSR7Worker::class,
            PSR7WorkerInterface::class,
            HttpWorker::class,
        ]);
    }
}