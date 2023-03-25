<?php

namespace Tests;

use DI\ContainerBuilder;
use Monolog\Handler\TestHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use Sicet7\Base\Logger\Module as LoggerModule;

#[CoversClass(LoggerModule::class)]
final class LoggerModuleTest extends ContainerTestCase
{
    /**
     * @param ContainerBuilder $builder
     * @return void
     * @throws \Exception
     */
    public static function addDefinitions(ContainerBuilder $builder): void
    {
        $builder->addDefinitions(LoggerModule::getDefinitions());
    }

    #[Test]
    public function canGetAllRegistrations(): void
    {
        $this->checkRegistrations([
            Logger::class,
            LoggerInterface::class,
        ]);
    }

    #[Test]
    public function canLogToMonolog(): void
    {
        $logger = self::getContainer()->get(Logger::class);

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