<?php

namespace Sicet7\Base\Factories;

use DI\Definition\FactoryDefinition;
use DI\DependencyException;
use DI\Factory\RequestedEntry;
use Invoker\ParameterResolver\ParameterResolver;

final class AutowireFactory
{
    public function __construct(
        private readonly ParameterResolver $resolver
    ) {
    }

    public function create(
        RequestedEntry $entry,
        ?FactoryDefinition $factoryDefinition = null,
        array $parameters = []
    ): mixed {
        $entryName = $entry->getName();
        return $this->make(
            $entryName,
            array_merge($factoryDefinition->getParameters(), $parameters)
        );
    }

    /**
     * @param string $class
     * @param array $parameters
     * @return mixed
     * @throws DependencyException
     */
    public function make(
        string $class,
        array $parameters = []
    ): mixed {
        try {
            if (!method_exists($class, '__construct')) {
                return new $class();
            }
            $args = $this->resolver->getParameters(
                new \ReflectionMethod($class, '__construct'),
                $parameters,
                []
            );
            ksort($args);
            return new $class(...$args);
        } catch (\ReflectionException $exception) {
            throw new DependencyException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}