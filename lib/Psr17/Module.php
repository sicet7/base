<?php

namespace Sicet7\Base\Psr17;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Sicet7\Base\ModuleInterface;
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
            Psr17Factory::class => create(Psr17Factory::class),
            RequestFactoryInterface::class => get(Psr17Factory::class),
            ResponseFactoryInterface::class => get(Psr17Factory::class),
            ServerRequestFactoryInterface::class => get(Psr17Factory::class),
            StreamFactoryInterface::class => get(Psr17Factory::class),
            UploadedFileFactoryInterface::class => get(Psr17Factory::class),
            UriFactoryInterface::class => get(Psr17Factory::class),
        ];
    }
}