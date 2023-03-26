<?php

namespace Sicet7\Base\Http\Attributes\Routing;

use Attribute;
use Sicet7\Base\Http\Enums\Method;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    /**
     * @var Method[]
     */
    public readonly array $methods;

    /**
     * Route constructor.
     * @param string $pattern
     * @param Method ...$methods
     */
    public function __construct(
        public readonly string $pattern,
        Method ...$methods,
    ) {
        $this->methods = $methods;
    }

    /**
     * @return string[]
     */
    public function getMethodsAsStringArray(): array
    {
        $methods = [];
        foreach ($this->methods as $method) {
            $methods[] = $method->value;
        }
        return $methods;
    }
}