<?php

namespace Sicet7\Base\Logger;

use Monolog\Logger;
use Psr\Log\LoggerInterface;
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
            Logger::class => create(Logger::class)
                ->constructor('main'),
            LoggerInterface::class => get(Logger::class),
        ];
    }
}