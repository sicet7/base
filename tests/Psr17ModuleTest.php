<?php

namespace Tests;

use DI\ContainerBuilder;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Sicet7\Base\Psr17\Module as Psr17Module;

#[CoversClass(Psr17Module::class)]
final class Psr17ModuleTest extends ContainerTestCase
{
    /**
     * @param ContainerBuilder $builder
     * @return void
     * @throws \Exception
     */
    public static function addDefinitions(ContainerBuilder $builder): void
    {
        $builder->addDefinitions(Psr17Module::getDefinitions());
    }

    #[Test]
    public function canGetAllRegistrations(): void
    {
        $this->checkRegistrations([
            Psr17Factory::class,
            RequestFactoryInterface::class,
            ResponseFactoryInterface::class,
            ServerRequestFactoryInterface::class,
            StreamFactoryInterface::class,
            UploadedFileFactoryInterface::class,
            UriFactoryInterface::class,
        ]);
    }
}