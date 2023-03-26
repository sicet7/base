<?php

namespace Sicet7\Base\Http\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class Middleware
{
    public function __construct(
        public string $class
    ) {
    }
}