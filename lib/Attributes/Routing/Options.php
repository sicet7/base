<?php

namespace Sicet7\Base\Attributes\Routing;

use Attribute;
use Sicet7\Base\Enums\Method;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Options extends Route
{
    /**
     * Options constructor.
     * @param string $pattern
     */
    public function __construct(string $pattern)
    {
        parent::__construct($pattern, Method::OPTIONS);
    }
}