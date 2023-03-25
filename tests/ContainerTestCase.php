<?php

namespace Tests;

use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

abstract class ContainerTestCase extends TestCase
{
    private static ?ContainerInterface $container;

    /**
     * @param ContainerBuilder $builder
     * @return void
     */
    public static function addDefinitions(ContainerBuilder $builder): void
    {
    }

    /**
     * @return void
     * @throws \Exception
     */
    final public static function setUpBeforeClass(): void
    {
        $builder = new ContainerBuilder();
        static::addDefinitions($builder);
        $builder->useAttributes(true);
        self::$container = $builder->build();
    }

    /**
     * @return void
     */
    final public static function tearDownAfterClass(): void
    {
        self::$container = null;
    }

    /**
     * @return ContainerInterface
     */
    final public static function getContainer(): ContainerInterface
    {
        return self::$container;
    }

    /**
     * @param array $registrations
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    final public function checkRegistrations(array $registrations): void
    {
        foreach ($registrations as $class) {
            $this->assertInstanceOf($class, self::getContainer()->get($class));
        }
    }
}