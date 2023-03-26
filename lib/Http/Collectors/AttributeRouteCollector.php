<?php

namespace Sicet7\Base\Http\Collectors;

use Psr\Http\Server\MiddlewareInterface;
use Sicet7\Base\Http\Attributes\Controller;
use Sicet7\Base\Http\Attributes\Middleware;
use Sicet7\Base\Http\Attributes\Routing\Route;
use Slim\Interfaces\RouteCollectorInterface;
use Slim\Interfaces\RouteCollectorProxyInterface;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Interfaces\RouteInterface;
use Slim\Routing\RouteCollector;

final class AttributeRouteCollector extends RouteCollector
{
    /**
     * @param \ReflectionClass|\ReflectionMethod $reflection
     * @return void
     */
    public function auto(
        \ReflectionClass|\ReflectionMethod $reflection
    ): void {
        if ($reflection instanceof \ReflectionMethod) {
            self::mapFromReflection($reflection, $this);
            return;
        }
        $controllerAttributes = $reflection->getAttributes(
            Controller::class,
            \ReflectionAttribute::IS_INSTANCEOF
        );

        if (empty($controllerAttributes)) {
            self::mapFromReflection($reflection, $this);
        } else {
            /** @var Controller $controllerAttribute */
            $controllerAttribute = $controllerAttributes[array_key_first($controllerAttributes)]->newInstance();
            $groupMiddlewares = $this->findMiddlewares($reflection);
            $controllerClassMethods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
            $group = $this->group(
                $controllerAttribute->prefix,
                function (
                    RouteCollectorProxyInterface $proxy
                ) use ($controllerClassMethods) {
                    foreach ($controllerClassMethods as $classMethod) {
                        self::mapFromReflection($classMethod, $proxy);
                    }
                }
            );
            self::addMiddlewares($group, $groupMiddlewares);
        }
    }

    /**
     * @param \ReflectionClass|\ReflectionMethod $reflection
     * @param RouteCollectorInterface|RouteCollectorProxyInterface $collector
     * @return void
     */
    private static function mapFromReflection(
        \ReflectionClass|\ReflectionMethod $reflection,
        RouteCollectorInterface|RouteCollectorProxyInterface $collector
    ): void {
        $target = (
            $reflection instanceof \ReflectionClass ?
                $reflection->name :
                [$reflection->class, $reflection->name]
        );

        $middlewares = self::findMiddlewares($reflection);

        $routeAttributes = $reflection->getAttributes(
            Route::class,
            \ReflectionAttribute::IS_INSTANCEOF
        );

        foreach ($routeAttributes as $routeAttribute) {
            /** @var Route $routeAttributeInstance */
            $routeAttributeInstance = $routeAttribute->newInstance();
            $route = $collector->map(
                $routeAttributeInstance->getMethodsAsStringArray(),
                $routeAttributeInstance->pattern,
                $target
            );
            self::addMiddlewares($route, $middlewares);
        }
    }

    /**
     * @param \ReflectionClass|\ReflectionMethod $reflection
     * @return array
     */
    private static function findMiddlewares(\ReflectionClass|\ReflectionMethod $reflection): array
    {
        $middlewares = [];
        $middlewareAttributes = $reflection->getAttributes(
            Middleware::class,
            \ReflectionAttribute::IS_INSTANCEOF
        );
        foreach ($middlewareAttributes as $middlewareAttribute) {
            /** @var Middleware $instance */
            $middlewareAttributeInstance = $middlewareAttribute->newInstance();
            $middlewares[] = $middlewareAttributeInstance->class;
        }

        $middlewareImplementingAttributes = $reflection->getAttributes(
            MiddlewareInterface::class,
            \ReflectionAttribute::IS_INSTANCEOF
        );
        foreach ($middlewareImplementingAttributes as $middlewareAttribute) {
            $middlewares[] = $middlewareAttribute->newInstance();
        }
        return $middlewares;
    }

    /**
     * @param RouteInterface|RouteGroupInterface $target
     * @param array $middlewares
     * @return void
     */
    private static function addMiddlewares(
        RouteInterface|RouteGroupInterface $target,
        array $middlewares
    ): void {
        foreach ($middlewares as $middleware) {
            $target->add($middleware);
        }
    }
}