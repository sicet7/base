<?php

namespace Sicet7\Base;

use DI\Container;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

abstract class DIApplication
{
    protected const CONTAINER_INSTANCE = Container::class;

    /**
     * @var ContainerInterface
     */
    protected readonly ContainerInterface $container;

    /**
     * @return static
     * @throws \Exception
     */
    final public static function instantiate(): static
    {
        return new static();
    }

    /**
     * @throws \Exception
     */
    private function __construct()
    {
        $builder = new ContainerBuilder(self::CONTAINER_INSTANCE);
        $this->applyDefinitions($builder);
        $this->container = $builder->build();
    }

    abstract protected function applyDefinitions(ContainerBuilder $builder): void;
}