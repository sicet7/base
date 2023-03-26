<?php

namespace Sicet7\Base\Http\Attributes\Routing;

use Attribute;
use Sicet7\Base\Http\Enums\Method;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Any extends Route
{
    /**
     * Any constructor.
     * @param string $pattern
     */
    public function __construct(string $pattern)
    {
        parent::__construct(
            $pattern,
            Method::GET,
            Method::POST,
            Method::PUT,
            Method::PATCH,
            Method::DELETE,
            Method::OPTIONS,
        );
    }
}